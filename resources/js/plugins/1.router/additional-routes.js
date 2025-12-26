export const redirects = [
  {
    path: '/',
    name: 'index',
    redirect: to => {
      const userData = useCookie('userData')
      if (userData)
        return { name: 'home' }

      return { name: 'login', query: to.query }
    },
  },
]
export const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/pages/home.vue'),
  },
  {
    path: '/fichas/index',
    name: 'fichas-index',
    component: () => import('@/pages/fichas-epi/Create.vue'),
  },
  {
    path: '/fichas/create',
    name: 'fichas-create',
    component: () => import('@/pages/fichas-epi/Index.vue'),
  },
  {
    path: '/fichas-epi/details/:id',
    name: 'fichas-details',
    component: () => import('@/pages/fichas-epi/details.vue'),
  },
  {
    path: '/romaneio-entrega',
    name: 'romaneio-entrega-index',
    component: () => import('@/pages/romaneio-entrega/Index.vue'),
  },
]
