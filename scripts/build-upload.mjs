import { cp, rm, mkdir } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.dirname(path.dirname(fileURLToPath(import.meta.url)));
const distDir = path.join(root, 'dist');
const uploadDir = path.join(root, 'dist-upload');

const EXCLUDED_TOP_LEVEL_DIRS = [
  'artigos',
  'aviso-de-afiliados',
  'noticias',
  'curiosidades',
  'cuidados-com-seu-animal',
  'content',
  'politica-de-privacidade',
  'sobre',
];

const EXCLUDED_LABEL =
  'index.html, sitemap estatico, artigos, paginas institucionais estaticas, produtos-que-amamos HTML estatico, noticias, curiosidades, cuidados-com-seu-animal, content';

function shouldCopy(src) {
  const rel = path.relative(distDir, src);
  if (rel === '') return true;

  const parts = rel.split(path.sep);
  const [first] = parts;

  if (rel === 'index.html') {
    return false;
  }

  if (rel === 'sitemap-index.xml' || rel === 'sitemap-0.xml') {
    return false;
  }

  if (parts[0] === 'admin' && parts[1] === '_data' && parts.length > 2) {
    return parts[2] === '.htaccess' || parts[2] === '.gitkeep';
  }

  if (EXCLUDED_TOP_LEVEL_DIRS.includes(first)) {
    return false;
  }

  // Mantem produtos-que-amamos/index.php, mas remove o HTML estatico
  // e as paginas de produto geradas pelo Astro nessa mesma pasta.
  if (first === 'produtos-que-amamos') {
    if (parts.length === 1) return true;
    return parts.length === 2 && parts[1] === 'index.php';
  }

  return true;
}

if (!existsSync(distDir)) {
  console.error('Pasta dist/ nao encontrada. Rode "npm run build" antes.');
  process.exit(1);
}

await rm(uploadDir, { recursive: true, force: true });
await mkdir(uploadDir, { recursive: true });

await cp(distDir, uploadDir, {
  recursive: true,
  filter: shouldCopy,
});

console.log(`Pasta gerada em dist-upload/ (sem: ${EXCLUDED_LABEL}).`);
console.log('Envie o CONTEUDO de dist-upload/ para public_html (nao a pasta em si).');
