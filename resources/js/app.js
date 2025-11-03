import './bootstrap';
import { createApp } from 'vue';
import ReAssignLeads from './components/ReAssignLeads.vue';

const mountElement = document.getElementById('admin-app');

if (mountElement) {
    const app = createApp({});
    app.component('re-assign-leads', ReAssignLeads);
    app.mount(mountElement);
}
