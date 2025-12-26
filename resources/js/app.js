import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import router from './router';
import store from './router/store';
import App from './App.vue';
import axios from 'axios';

// Configurar axios para usar o token de autenticação
axios.defaults.baseURL = '/api';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['Content-Type'] = 'application/json';

// Inicializar autenticação
store.initAuth();

const app = createApp(App);

app.use(router);
app.mount('#app');

