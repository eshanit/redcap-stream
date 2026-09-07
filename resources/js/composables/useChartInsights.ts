import * as ss from 'simple-statistics';

/**
 * Generates plain-English descriptive paragraphs (trend, average, spread,
 * skewness, concentration, comparison, naive projection) from the chart
 * data already loaded on a data6 deep-dive page. Everything here is
 * arithmetic over numbers the page already has - no extra network calls.
 *
 * Every generator returns a single string with paragraphs separated by
 * "\n\n"; render by splitting on that separator into <p> tags.
 */

export interface Unit {
    unit: 'count' | 'percent';
    /** e.g. "adolescents", "visits", "sessions" - what one unit represents */
    noun: string;
}

function round1(v: number): number {
    return Math.round(v * 10) / 10;
}

function fmt(v: number, unit: Unit['unit']): string {
    return unit === 'percent' ? `${round1(v)}%` : Math.round(v).toLocaleString();
}

function pctOf(part: number, whole: number): number {
    return whole > 0 ? round1((part / whole) * 100) : 0;
}

/**
 * Coefficient of variation, as a percentage - 0 when there's no data or the
 * mean is 0. simple-statistics' mean() throws on an empty array (e.g. a
 * period with zero males), so this must never call it unguarded.
 */
function coefVar(values: number[]): number {
    if (values.length === 0) return 0;
    const m = ss.mean(values);

    return m !== 0 ? round1((ss.standardDeviation(values) / Math.abs(m)) * 100) : 0;
}

/** ss.mean() guarded against the empty-array case, which it throws on. */
function safeMean(values: number[]): number {
    return values.length === 0 ? 0 : ss.mean(values);
}

function plural(word: string): string {
    return /[^aeiou]y$/i.test(word) ? `${word.slice(0, -1)}ies` : `${word}s`;
}

function skewLabel(skew: number): string {
    const mag = Math.abs(skew);
    const strength = mag < 0.5 ? 'roughly symmetric' : mag < 1 ? 'moderately skewed' : 'strongly skewed';
    if (mag < 0.5) return strength;

    return `${strength} ${skew > 0 ? 'toward a few unusually high values' : 'toward a few unusually low values'}`;
}

/**
 * Runs a narrative generator and swallows any error into an empty string.
 * These functions are supplementary text under a chart that has already
 * rendered its real numbers - across 45 indicators and many disaggregation
 * combinations there will always be another sparse-data numeric edge case
 * (an all-one-sex period, an all-zero facility, ...). One generator ever
 * throwing froze an entire deep-dive page in production (an uncaught error
 * inside a Vue computed aborts that render with no built-in fallback), so
 * every entry point is wrapped here rather than trusting each call site to
 * guard every simple-statistics call correctly.
 */
function safely(label: string, fn: () => string): string {
    try {
        return fn();
    } catch (error) {
        console.error(`[useChartInsights] ${label} failed, showing no insight instead of crashing the page:`, error);

        return '';
    }
}

/**
 * A single ordered numeric series (a monthly trend). `denominators`, when
 * given (percent indicators), lets the narrative caveat small samples.
 */
export function describeTrend(labels: string[], rawValues: (number | null)[], opts: Unit, denominators?: (number | undefined)[]): string {
    return safely('describeTrend', () => describeTrendImpl(labels, rawValues, opts, denominators));
}

function describeTrendImpl(labels: string[], rawValues: (number | null)[], opts: Unit, denominators?: (number | undefined)[]): string {
    const points: { i: number; label: string; v: number }[] = [];
    rawValues.forEach((v, i) => {
        if (v !== null && v !== undefined) points.push({ i, label: labels[i], v });
    });
    if (points.length < 2) return '';

    const values = points.map((p) => p.v);
    const n = values.length;
    const mean = ss.mean(values);
    const cv = coefVar(values);
    const { m: slope, b: intercept } = ss.linearRegression(values.map((v, i) => [i, v]));
    const maxPoint = points[values.indexOf(Math.max(...values))];
    const minPoint = points[values.indexOf(Math.min(...values))];
    const first = points[0];
    const last = points[points.length - 1];

    const monthlyChangeShare = mean !== 0 ? Math.abs(slope) / Math.abs(mean) : 0;
    const direction = monthlyChangeShare < 0.03 ? 'held roughly steady' : slope > 0 ? 'trended upward' : 'trended downward';

    let p1 = `From ${first.label} to ${last.label}, ${opts.noun} ${direction}, averaging ${fmt(mean, opts.unit)} per month `
        + `(low of ${fmt(minPoint.v, opts.unit)} in ${minPoint.label}, high of ${fmt(maxPoint.v, opts.unit)} in ${maxPoint.label}).`;
    if (direction !== 'held roughly steady') {
        if (opts.unit === 'percent') {
            // A rate's own change is expressed in percentage points, not a
            // relative "% of %" (194% swings on small base rates mislead).
            const diffPoints = round1(last.v - first.v);
            p1 += ` ${last.label} is ${Math.abs(diffPoints)} percentage points ${diffPoints >= 0 ? 'higher than' : 'lower than'} ${first.label}, `
                + `a change of about ${fmt(Math.abs(slope), opts.unit)} per month on average.`;
        } else if (first.v !== 0) {
            const totalChangePct = pctOf(last.v - first.v, Math.abs(first.v));
            p1 += ` ${last.label} is ${Math.abs(totalChangePct)}% ${totalChangePct >= 0 ? 'above' : 'below'} ${first.label}, `
                + `a change of about ${fmt(Math.abs(slope), opts.unit)} per month on average.`;
        }
    }
    p1 += cv < 20
        ? ' Month-to-month values are fairly consistent.'
        : cv < 50
            ? ' There is moderate month-to-month variability.'
            : ' Month-to-month values swing considerably, so any single month should be read with caution.';

    const parts = [p1];

    if (n >= 3) {
        const skew = ss.sampleSkewness(values);
        let p2 = `The distribution across months is ${skewLabel(skew)}.`;

        const nextIndex = points[points.length - 1].i + 1;
        const projected = intercept + slope * nextIndex;
        const clamped = opts.unit === 'percent' ? Math.min(100, Math.max(0, projected)) : Math.max(0, projected);
        p2 += ` Extrapolating the current linear trend, the next month would land around ${fmt(clamped, opts.unit)} — `
            + 'a rough guide only, not a forecast, since a single new data point can shift it substantially.';

        if (opts.unit === 'percent' && denominators) {
            const validDenoms = denominators.filter((d): d is number => d !== undefined);
            if (validDenoms.length && ss.mean(validDenoms) < 15) {
                p2 += ' These rates are based on small monthly numbers, so swings from month to month are expected and not necessarily meaningful.';
            }
        } else if (last.v < mean - ss.standardDeviation(values) && last.i === Math.max(...points.map((p) => p.i))) {
            p2 += ' The most recent month sits well below average — worth checking whether this reflects a real drop or data not yet synced from the field.';
        }

        parts.push(p2);
    }

    return parts.join('\n\n');
}

/** An unordered categorical breakdown (facility, district, service point). */
export function describeCategorical(items: { label: string; value: number | null }[], opts: Unit & { dimension: string }): string {
    return safely('describeCategorical', () => describeCategoricalImpl(items, opts));
}

function describeCategoricalImpl(items: { label: string; value: number | null }[], opts: Unit & { dimension: string }): string {
    const clean = items.filter((i): i is { label: string; value: number } => i.value !== null).filter((i) => i.value > 0);
    if (clean.length < 2) return '';

    const values = clean.map((i) => i.value);
    const total = ss.sum(values);
    const mean = ss.mean(values);
    const sorted = [...clean].sort((a, b) => b.value - a.value);
    const top = sorted[0];
    const topShare = pctOf(top.value, total);
    const top3 = sorted.slice(0, Math.min(3, sorted.length));
    const top3Share = pctOf(ss.sum(top3.map((i) => i.value)), total);
    const bottom = sorted[sorted.length - 1];

    let p1 = `Across ${clean.length} ${plural(opts.dimension)}, ${top.label} accounts for the largest share at ${fmt(top.value, opts.unit)}`
        + (opts.unit === 'count' ? ` (${topShare}% of the total)` : '') + '.';
    if (clean.length >= 3) {
        p1 += ` The top ${top3.length} ${plural(opts.dimension)} together (${top3.map((i) => i.label).join(', ')}) `
            + `make up ${top3Share}% of all activity, while ${bottom.label} has the least at ${fmt(bottom.value, opts.unit)}.`;
    }

    const cv = coefVar(values);
    let p2 = cv < 30
        ? `Activity is fairly evenly spread across ${plural(opts.dimension)} (average ${fmt(mean, opts.unit)}, low variability).`
        : `Activity is unevenly spread across ${plural(opts.dimension)} (average ${fmt(mean, opts.unit)}, but individual ${plural(opts.dimension)} vary widely around it).`;

    if (clean.length >= 3) {
        const skew = ss.sampleSkewness(values);
        if (Math.abs(skew) >= 0.5) {
            p2 += ` The distribution is ${skewLabel(skew)} — a small number of ${plural(opts.dimension)} drive most of the ${opts.unit === 'percent' ? 'rate' : 'volume'}, `
                + `which also concentrates data-quality risk in those ${plural(opts.dimension)}.`;
        }
    }

    return [p1, p2].join('\n\n');
}

interface SexPoint { label: string; male: number | null; female: number | null; }

/** Male/female comparison, adapting language for an ordered (monthly) vs unordered (categorical) axis. */
export function describeSexSplit(items: SexPoint[], ordered: boolean, opts: Unit & { dimension: string }): string {
    return safely('describeSexSplit', () => describeSexSplitImpl(items, ordered, opts));
}

function describeSexSplitImpl(items: SexPoint[], ordered: boolean, opts: Unit & { dimension: string }): string {
    const clean = items.filter((i) => i.male !== null || i.female !== null);
    if (clean.length < 2) return '';

    const maleVals = clean.map((i) => i.male ?? 0);
    const femaleVals = clean.map((i) => i.female ?? 0);
    const maleTotal = ss.sum(maleVals);
    const femaleTotal = ss.sum(femaleVals);
    const grandTotal = maleTotal + femaleTotal;
    const femaleShare = pctOf(femaleTotal, grandTotal);

    let p1: string;
    if (opts.unit === 'percent') {
        const maleMean = safeMean(maleVals.filter((_, i) => clean[i].male !== null));
        const femaleMean = safeMean(femaleVals.filter((_, i) => clean[i].female !== null));
        p1 = `Averaged across ${plural(opts.dimension)}, the rate is ${fmt(femaleMean, 'percent')} for females and ${fmt(maleMean, 'percent')} for males.`;
    } else {
        p1 = `Females account for ${femaleShare}% of the total (${fmt(femaleTotal, 'count')} female, ${fmt(maleTotal, 'count')} male).`;
    }

    if (ordered) {
        const idx = clean.map((_, i) => i);
        const femaleShareByPoint = clean.map((c) => {
            const t = (c.male ?? 0) + (c.female ?? 0);

            return t > 0 ? ((c.female ?? 0) / t) * 100 : null;
        });
        const validShares = femaleShareByPoint.filter((v): v is number => v !== null);
        if (validShares.length >= 3) {
            const { m: slope } = ss.linearRegression(idx.filter((i) => femaleShareByPoint[i] !== null).map((i) => [i, femaleShareByPoint[i] as number]));
            const trendWord = Math.abs(slope) < 0.5 ? 'has stayed fairly stable' : slope > 0 ? 'has been rising' : 'has been falling';
            p1 += ` The female share of the total ${trendWord} over the period (from ${round1(validShares[0])}% to ${round1(validShares[validShares.length - 1])}%).`;
        }
        const maleCv = coefVar(maleVals.filter((v) => v > 0));
        const femaleCv = coefVar(femaleVals.filter((v) => v > 0));
        if (Math.abs(maleCv - femaleCv) > 15) {
            p1 += ` Month-to-month, the ${maleCv > femaleCv ? 'male' : 'female'} count is noticeably more volatile than the ${maleCv > femaleCv ? 'female' : 'male'} count.`;
        }
    } else {
        // A minimum-N guard: "zero of one sex" is only worth flagging once a
        // category has enough volume that the gap isn't just small-sample
        // noise (e.g. a facility with 6 clients total proves nothing).
        const MIN_N_FOR_ZERO_FLAG = 10;
        const zeroMale = clean.filter((c) => (c.male ?? 0) === 0 && (c.female ?? 0) >= MIN_N_FOR_ZERO_FLAG);
        const zeroFemale = clean.filter((c) => (c.female ?? 0) === 0 && (c.male ?? 0) >= MIN_N_FOR_ZERO_FLAG);
        const skewed = clean
            .map((c) => {
                const t = (c.male ?? 0) + (c.female ?? 0);

                return { label: c.label, femaleShare: t > 0 ? ((c.female ?? 0) / t) * 100 : null };
            })
            .filter((c) => c.femaleShare !== null) as { label: string; femaleShare: number }[];
        const mostMale = [...skewed].sort((a, b) => a.femaleShare - b.femaleShare)[0];
        const mostFemale = [...skewed].sort((a, b) => b.femaleShare - a.femaleShare)[0];

        if (mostMale && mostFemale && mostMale.label !== mostFemale.label && Math.abs(mostMale.femaleShare - mostFemale.femaleShare) > 30) {
            p1 += ` The sex mix varies notably by ${opts.dimension}: ${mostFemale.label} is ${round1(mostFemale.femaleShare)}% female, `
                + `while ${mostMale.label} is only ${round1(mostMale.femaleShare)}% female.`;
        }
        if (zeroMale.length > 0) {
            p1 += ` ${zeroMale.length === 1 ? zeroMale[0].label : `${zeroMale.length} ${plural(opts.dimension)}`} recorded no male ${opts.unit === 'percent' ? 'cases' : opts.noun} at all — worth checking whether that reflects real access or a reporting gap.`;
        } else if (zeroFemale.length > 0) {
            p1 += ` ${zeroFemale.length === 1 ? zeroFemale[0].label : `${zeroFemale.length} ${plural(opts.dimension)}`} recorded no female ${opts.unit === 'percent' ? 'cases' : opts.noun} at all.`;
        }
    }

    return p1;
}
