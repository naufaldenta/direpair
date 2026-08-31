import type { APIRoute } from 'astro';
import { getCmsDataset, metaBoolean } from '../lib/cms';

export const prerender = true;

const escapeXml = (value: string): string => value
  .replaceAll('&', '&amp;')
  .replaceAll('<', '&lt;')
  .replaceAll('>', '&gt;')
  .replaceAll('"', '&quot;')
  .replaceAll("'", '&apos;');

export const GET: APIRoute = async ({ site }) => {
  const origin = (site ?? new URL('http://localhost:4321')).origin;
  const dataset = await getCmsDataset();
  const paths: string[] = [];

  if (!(import.meta.env.PROD && dataset.source === 'fixture')) {
    paths.push('/', '/booking/', '/faq/', '/harga/', '/kontak/', '/layanan/', '/masalah/', '/tentang/');
    paths.push(...dataset.services.map((item) => `/layanan/${item.slug}/`));
    paths.push(...dataset.problems.map((item) => `/masalah/${item.slug}/`));
    if (dataset.technicians.length > 0) paths.push('/tim/');
    if (dataset.locations.length > 0) paths.push('/lokasi/');
    if (dataset.warranties.length > 0) paths.push('/garansi/');
    if (dataset.repairCases.some((item) => metaBoolean(item, 'publish_consent'))) paths.push('/kasus/');
    if (dataset.policies.some((item) => item.slug === 'privacy')) paths.push('/privacy/');
    if (dataset.policies.some((item) => item.slug === 'terms')) paths.push('/terms/');
  }

  const urls = Array.from(new Set(paths)).map((path) => `<url><loc>${escapeXml(new URL(path, origin).toString())}</loc></url>`).join('');
  return new Response(`<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">${urls}</urlset>`, {
    headers: { 'Content-Type': 'application/xml; charset=utf-8' },
  });
};
