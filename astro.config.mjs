import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

export default defineConfig({
  site: 'https://pedritaethor.com',
  output: 'static',
  integrations: [sitemap()],
});
