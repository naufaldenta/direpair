import type { APIRoute } from 'astro';

export const prerender = true;

export const GET: APIRoute = ({ site }) => {
  const origin = (site ?? new URL('http://localhost:4321')).origin;
  return new Response(`User-agent: *\nAllow: /\nDisallow: /cek-status/\nDisallow: /mock-payment/\nSitemap: ${origin}/sitemap.xml\n`, {
    headers: { 'Content-Type': 'text/plain; charset=utf-8' },
  });
};
