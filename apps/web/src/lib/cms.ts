import { z } from 'zod';
import { fixtureDataset } from '../data/fixtures';
import type { CmsContent, CmsDataset, SiteSettings } from './cms-types';

const imageSchema = z.object({ id: z.number(), url: z.string(), width: z.number(), height: z.number(), alt: z.string() });
const termSchema = z.object({ id: z.number(), slug: z.string(), name: z.string() });
const termsSchema = z.preprocess(
  (value) => Array.isArray(value) && value.length === 0 ? {} : value,
  z.record(z.string(), z.array(termSchema)),
);
const contentSchema = z.object({
  id: z.number(),
  uuid: z.string(),
  type: z.string(),
  slug: z.string(),
  title: z.string(),
  excerpt: z.string(),
  content: z.string(),
  featured_image: imageSchema.nullable(),
  meta: z.record(z.string(), z.unknown()),
  // Older Direpair CMS releases encoded an empty PHP array as JSON `[]` for
  // content types without taxonomies (for example FAQ). Normalize only that
  // known empty shape while continuing to reject malformed non-empty arrays.
  terms: termsSchema,
  published_at: z.string(),
  modified_at: z.string(),
});
const settingsSchema = z.object({
  business_name: z.string(), tagline: z.string(), phone: z.string(), whatsapp: z.string(), email: z.string(), street_address: z.string(),
  city: z.string(), region: z.string(), postal_code: z.string(), country_code: z.string(), opening_hours_summary: z.string(),
  booking_path: z.string(), status_path: z.string(), instagram_url: z.string(), youtube_url: z.string(), facebook_url: z.string(),
  default_pricing_disclaimer: z.string(), demo_mode: z.boolean(),
});

let datasetPromise: Promise<CmsDataset> | undefined;

const fetchJson = async (url: string): Promise<unknown> => {
  try {
    const response = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    return response.json();
  } catch (error) {
    const detail = error instanceof Error ? error.message : 'unknown network error';
    throw new Error(`CMS production tidak dapat dibaca dari ${url}: ${detail}`);
  }
};

const fetchCollection = async (baseUrl: string, route: string): Promise<CmsContent[]> => {
  const payload = z.object({ data: z.array(contentSchema) }).parse(await fetchJson(`${baseUrl}/${route}?per_page=100`));
  return payload.data as CmsContent[];
};

const loadWordPress = async (baseUrl: string): Promise<CmsDataset> => {
  // Shared cPanel hosting can queue concurrent PHP requests. Fetching these
  // collections sequentially keeps the static build reliable without a long-
  // running Node process or an aggressive abort timer on Windows.
  const settingsPayload = await fetchJson(`${baseUrl}/site-settings`);
  const services = await fetchCollection(baseUrl, 'services');
  const problems = await fetchCollection(baseUrl, 'problems');
  const faqs = await fetchCollection(baseUrl, 'faqs');
  const technicians = await fetchCollection(baseUrl, 'technicians');
  const locations = await fetchCollection(baseUrl, 'locations');
  const repairCases = await fetchCollection(baseUrl, 'repair-cases');
  const warranties = await fetchCollection(baseUrl, 'warranties');
  const policies = await fetchCollection(baseUrl, 'policies');
  const settings = z.object({ data: settingsSchema }).parse(settingsPayload).data as SiteSettings;
  const publishable = (items: CmsContent[]): CmsContent[] => import.meta.env.PROD ? items.filter(isTrustedPublicContent) : items;
  return {
    source: 'wordpress', settings,
    services: publishable(services), problems: publishable(problems), faqs: publishable(faqs), technicians: publishable(technicians),
    locations: publishable(locations), repairCases: publishable(repairCases), warranties: publishable(warranties), policies: publishable(policies),
  };
};

export const getCmsDataset = (): Promise<CmsDataset> => {
  if (datasetPromise) return datasetPromise;
  datasetPromise = (async () => {
    const baseUrl = import.meta.env.CMS_BASE_URL?.replace(/\/$/, '');
    if (!baseUrl) return fixtureDataset;
    try {
      return await loadWordPress(baseUrl);
    } catch (error) {
      if (import.meta.env.CMS_FIXTURE_FALLBACK === 'false') throw error;
      console.warn('Direpair CMS unavailable; using marked fixture content.', error);
      return fixtureDataset;
    }
  })();
  return datasetPromise;
};

export const metaString = (content: CmsContent, key: string, fallback = ''): string => {
  const value = content.meta[key];
  return typeof value === 'string' ? value : fallback;
};

export const metaNumber = (content: CmsContent, key: string): number | null => {
  const value = content.meta[key];
  if (typeof value === 'number') return value;
  if (typeof value === 'string' && value !== '' && Number.isFinite(Number(value))) return Number(value);
  return null;
};

export const metaStrings = (content: CmsContent, key: string): string[] => {
  const value = content.meta[key];
  return Array.isArray(value) ? value.filter((item): item is string => typeof item === 'string') : [];
};

export const metaBoolean = (content: CmsContent, key: string): boolean => {
  const value = content.meta[key];
  return value === true || value === 1 || value === '1' || value === 'true';
};

export const isTrustedPublicContent = (content: CmsContent): boolean => {
  if (!import.meta.env.PROD) return true;
  return !metaBoolean(content, 'is_demo') && metaBoolean(content, 'is_verified');
};
