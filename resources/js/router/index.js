import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Lazy load components
const Home = () => import('../views/Home.vue');
const Login = () => import('../views/auth/Login.vue');
const Register = () => import('../views/auth/Register.vue');
const Chat = () => import('../views/Chat.vue');
const Profile = () => import('../views/Profile.vue');
const Discover = () => import('../views/Discover.vue');
const Live = () => import('../views/Live.vue');
const Rooms = () => import('../views/Rooms.vue');
const Games = () => import('../views/Games.vue');
const Karaoke = () => import('../views/Karaoke.vue');
const Shop = () => import('../views/Shop.vue');
const Settings = () => import('../views/Settings.vue');
const Ranking = () => import('../views/Ranking.vue');

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: { requiresAuth: true },
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { guest: true },
  },
  {
    path: '/chat',
    name: 'chat',
    component: Chat,
    meta: { requiresAuth: true },
  },
  {
    path: '/profile/:id?',
    name: 'profile',
    component: Profile,
    meta: { requiresAuth: true },
  },
  {
    path: '/discover',
    name: 'discover',
    component: Discover,
    meta: { requiresAuth: true },
  },
  {
    path: '/live',
    name: 'live',
    component: Live,
    meta: { requiresAuth: true },
  },
  {
    path: '/rooms',
    name: 'rooms',
    component: Rooms,
    meta: { requiresAuth: true },
  },
  {
    path: '/games',
    name: 'games',
    component: Games,
    meta: { requiresAuth: true },
  },
  {
    path: '/karaoke',
    name: 'karaoke',
    component: Karaoke,
    meta: { requiresAuth: true },
  },
  {
    path: '/shop',
    name: 'shop',
    component: Shop,
    meta: { requiresAuth: true },
  },
  {
    path: '/ranking',
    name: 'ranking',
    component: Ranking,
    meta: { requiresAuth: true },
  },
  {
    path: '/settings',
    name: 'settings',
    component: Settings,
    meta: { requiresAuth: true },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('../views/NotFound.vue'),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  },
});

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const isGuest = to.matched.some(record => record.meta.guest);

  if (requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' });
  } else if (isGuest && authStore.isAuthenticated) {
    next({ name: 'home' });
  } else {
    next();
  }
});

export default router;
