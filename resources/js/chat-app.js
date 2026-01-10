import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import { createI18n } from 'vue-i18n';
import vi from './locales/vi.json';
import en from './locales/en.json';
import App from './App.vue';

// Create i18n instance
const i18n = createI18n({
    legacy: false,
    locale: localStorage.getItem('locale') || 'vi',
    fallbackLocale: 'en',
    messages: {
        vi,
        en,
    },
});

// Create Pinia instance
const pinia = createPinia();

// Create Vue app
const app = createApp(App);

// Use plugins
app.use(pinia);
app.use(router);
app.use(i18n);

// Global error handler
app.config.errorHandler = (err, instance, info) => {
    console.error('Vue Error:', err, info);
};

// Mount app
app.mount('#app');
