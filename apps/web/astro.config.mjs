// @ts-check
import { defineConfig } from 'astro/config';
import vercel from '@astrojs/vercel';

const vercelProductionUrl = process.env.VERCEL_PROJECT_PRODUCTION_URL?.trim();
const productionSite = process.env.PUBLIC_SITE_URL?.trim()
  ?? (vercelProductionUrl ? `https://${vercelProductionUrl}` : 'https://direpair.id');

export default defineConfig({
  site: productionSite,
  output: 'static',
  adapter: vercel(),
  security: {
    checkOrigin: true,
  },
});
