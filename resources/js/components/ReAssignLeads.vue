<template>
    <div>
        <button class="reassign-trigger" type="button" @click="openModal" :disabled="loading">
            <span v-if="!loading">{{ buttonText }}</span>
            <span v-else>Processing...</span>
        </button>

        <transition name="reassign-fade">
            <div v-if="open" class="reassign-backdrop" @click.self="closeModal">
                <div class="reassign-modal">
                    <header>
                        <h3>Re-Assign Leads</h3>
                    </header>

                    <form @submit.prevent="submit">
                        <div class="reassign-body">
                            <p v-if="success" class="reassign-success">{{ success }}</p>
                            <p v-if="error" class="reassign-error">{{ error }}</p>

                            <div class="reassign-field">
                                <label for="reassign-leads-input">Lead IDs</label>
                                <textarea
                                    id="reassign-leads-input"
                                    v-model="leadInput"
                                    placeholder="Type IDs separated by commas or new lines"
                                ></textarea>
                                <small>Parsed leads: {{ normalizedLeads.length }}</small>
                            </div>

                            <div class="reassign-field">
                                <label for="reassign-user">Assign To</label>
                                <select id="reassign-user" v-model="form.user_id" :disabled="loading">
                                    <option value="">Select a user</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name || user.email }}
                                    </option>
                                </select>
                            </div>

                            <div class="reassign-field">
                                <label for="reassign-type">Lead Type</label>
                                <select id="reassign-type" v-model="form.type" :disabled="loading">
                                    <option value="">Keep current type</option>
                                    <option value="fresh">Fresh</option>
                                    <option value="cold_call">Cold Call</option>
                                </select>
                            </div>

                            <div class="reassign-checkboxes">
                                <label>
                                    <input type="checkbox" v-model="form.duplicateFresh" :disabled="loading">
                                    Duplicate as fresh lead
                                </label>
                                <label>
                                    <input type="checkbox" v-model="form.sameStage" :disabled="loading">
                                    Keep current stage
                                </label>
                                <label>
                                    <input type="checkbox" v-model="form.asSalesman" :disabled="loading">
                                    Assign as salesman
                                </label>
                                <label>
                                    <input type="checkbox" v-model="form.clearHistory" :disabled="loading">
                                    Clear activity history
                                </label>
                            </div>
                        </div>

                        <div class="reassign-footer">
                            <button type="button" class="cancel" @click="closeModal" :disabled="loading">
                                Cancel
                            </button>
                            <button type="submit" class="confirm" :disabled="loading">
                                <span v-if="!loading">Confirm</span>
                                <span v-else>Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'ReAssignLeads',

    props: {
        initialLeads: {
            type: Array,
            default: () => [],
        },
        buttonText: {
            type: String,
            default: 'Re-Assign Leads',
        },
    },

    data() {
        return {
            open: false,
            loading: false,
            users: [],
            leadInput: '',
            form: {
                user_id: '',
                type: '',
                duplicateFresh: false,
                sameStage: false,
                asSalesman: false,
                clearHistory: false,
            },
            error: '',
            success: '',
        };
    },

    computed: {
        normalizedLeads() {
            const tokens = this.leadInput
                .split(/\s|,/)
                .map((token) => token.trim())
                .filter((token) => token.length > 0);

            return tokens.filter((token) => /^\d+$/.test(token)).map((value) => Number(value));
        },
    },

    watch: {
        initialLeads: {
            handler(newValue) {
                if (Array.isArray(newValue) && newValue.length) {
                    this.leadInput = newValue.join(', ');
                }
            },
            immediate: true,
        },
    },

    mounted() {
        this.fetchUsers();
    },

    methods: {
        openModal() {
            this.open = true;
            this.error = '';
            this.success = '';

            if (!this.leadInput && Array.isArray(this.initialLeads) && this.initialLeads.length) {
                this.leadInput = this.initialLeads.join(', ');
            }
        },

        closeModal() {
            if (this.loading) {
                return;
            }

            this.open = false;
        },

        async fetchUsers() {
            try {
                const response = await axios.get('/admin/users/list');
                const data = Array.isArray(response.data) ? response.data : response.data?.data;

                this.users = Array.isArray(data) ? data : [];
            } catch (error) {
                console.error('Failed to fetch users', error);
                this.error = 'Unable to load users. Please refresh and try again.';
            }
        },

        async submit() {
            this.error = '';
            this.success = '';

            const leads = this.normalizedLeads;

            if (!leads.length) {
                this.error = 'Please provide at least one lead ID.';
                return;
            }

            if (!this.form.user_id) {
                this.error = 'Please select a user to assign the leads to.';
                return;
            }

            this.loading = true;

            try {
                await axios.post('/admin/leads/reassign', {
                    leads,
                    user_id: this.form.user_id,
                    type: this.form.type || undefined,
                    duplicateFresh: this.form.duplicateFresh,
                    sameStage: this.form.sameStage,
                    asSalesman: this.form.asSalesman,
                    clearHistory: this.form.clearHistory,
                });

                this.success = 'Leads reassigned successfully.';

                setTimeout(() => {
                    this.closeModal();
                    window.location.reload();
                }, 900);
            } catch (error) {
                if (error.response?.data?.message) {
                    this.error = error.response.data.message;
                } else if (error.response?.data?.errors) {
                    const messages = Object.values(error.response.data.errors).flat();
                    this.error = messages.join(' ');
                } else {
                    this.error = 'Something went wrong while saving.';
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
