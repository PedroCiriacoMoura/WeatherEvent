import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'

import { isAuthenticated } from '@/lib/auth'

export interface Breadcrumb {
  labelKey: string
  /** Route name to link to (omitted for the current page). */
  name?: string
}

declare module 'vue-router' {
  interface RouteMeta {
    requiresAuth?: boolean
    guestOnly?: boolean
    breadcrumbs?: Breadcrumb[]
  }
}

const routes: RouteRecordRaw[] = [
  { path: '/', redirect: '/dashboard' },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/RegisterView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/views/DashboardView.vue'),
        meta: { breadcrumbs: [{ labelKey: 'nav.dashboard' }] },
      },
      {
        path: 'events',
        name: 'events',
        component: () => import('@/views/EventsListView.vue'),
        meta: { breadcrumbs: [{ labelKey: 'nav.events' }] },
      },
      {
        path: 'events/create',
        name: 'event-create',
        component: () => import('@/views/EventCreateView.vue'),
        meta: {
          breadcrumbs: [
            { labelKey: 'nav.events', name: 'events' },
            { labelKey: 'events.create.title' },
          ],
        },
      },
      {
        path: 'events/:id(\\d+)',
        name: 'event-detail',
        component: () => import('@/views/EventDetailView.vue'),
        meta: {
          breadcrumbs: [
            { labelKey: 'nav.events', name: 'events' },
            { labelKey: 'events.detail.breadcrumb' },
          ],
        },
      },
      {
        path: 'events/:id(\\d+)/edit',
        name: 'event-edit',
        component: () => import('@/views/EventEditView.vue'),
        meta: {
          breadcrumbs: [
            { labelKey: 'nav.events', name: 'events' },
            { labelKey: 'events.edit.title' },
          ],
        },
      },
      {
        path: 'profile',
        name: 'profile',
        component: () => import('@/views/ProfileView.vue'),
        meta: { breadcrumbs: [{ labelKey: 'nav.profile' }] },
      },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to) => {
  const authenticated = isAuthenticated()

  if (to.meta.requiresAuth && !authenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guestOnly && authenticated) {
    return { name: 'dashboard' }
  }

  return true
})

export default router
