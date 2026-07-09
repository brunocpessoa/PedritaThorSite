export const site = {
  name: 'Pedrita & Thor',
  url: 'https://pedritaethor.com',
  description:
    'Conteudo pet com dicas de cuidados, curiosidades, noticias leves e produtos recomendados para quem ama cachorros.',
  socials: {
    instagram: 'https://www.instagram.com/pedritaethorstore?igsh=MThlZ3BxcnA1NmV5NQ%3D%3D',
    tiktok: 'https://www.tiktok.com/@pedritaethor',
    youtube: 'https://www.youtube.com/@pedritaethor',
  },
  store: {
    shopee: 'https://s.shopee.com.br/6VM8ojrAOa?share_channel_code=1',
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
