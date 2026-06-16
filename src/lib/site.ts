export const site = {
  name: 'Pedrita & Thor',
  url: 'https://pedritaethor.com',
  description:
    'Conteudo pet com dicas de cuidados, curiosidades, noticias leves e produtos recomendados para quem ama cachorros.',
  socials: {
    instagram: '#',
    tiktok: '#',
    youtube: '#',
  },
};

export const articleCategories = {
  cuidados: {
    title: 'Cuidados com seu animal',
    description:
      'Guias simples sobre saude, higiene, alimentacao, rotina e bem-estar para cachorros.',
    path: '/cuidados-com-seu-animal/',
  },
  curiosidades: {
    title: 'Curiosidades',
    description:
      'Conteudos leves, informativos e compartilhaveis sobre o mundo dos pets.',
    path: '/curiosidades/',
  },
  noticias: {
    title: 'Noticias',
    description:
      'Novidades do universo pet, tendencias, alertas e assuntos que valem acompanhar.',
    path: '/noticias/',
  },
} as const;
