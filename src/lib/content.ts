import { getCollection } from 'astro:content';

export async function getPublishedArticles() {
  const articles = await getCollection('articles', ({ data }) => !data.draft);

  return articles.sort(
    (a, b) => b.data.publishDate.getTime() - a.data.publishDate.getTime(),
  );
}

export async function getPublishedProducts() {
  const products = await getCollection('products', ({ data }) => !data.draft);

  return products.sort((a, b) => Number(b.data.featured) - Number(a.data.featured));
}

export function formatDate(date: Date) {
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  }).format(date);
}
