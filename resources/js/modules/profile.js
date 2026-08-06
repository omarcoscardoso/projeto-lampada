export const profileApp = () => ({
    showProfileModal: false,
    profileTab: 'stats', // 'stats' | 'info' | 'password'
    profileData: null,
    profileLoading: false,

    profileForm: {
        name: '',
    },
    profileSubmitting: false,
    profileMessage: null,
    profileError: null,

    passwordForm: {
        current_password: '',
        password: '',
        password_confirmation: '',
    },
    passwordSubmitting: false,
    passwordMessage: null,
    passwordError: null,
    passwordFieldErrors: {},

    openProfileModal(tab = 'stats') {
        this.profileTab = tab;
        this.showProfileModal = true;
        this.profileMessage = null;
        this.profileError = null;
        this.passwordMessage = null;
        this.passwordError = null;
        this.passwordFieldErrors = {};
        this.fetchProfileData();
    },

    closeProfileModal() {
        this.showProfileModal = false;
    },

    async fetchProfileData() {
        this.profileLoading = true;
        try {
            const res = await fetch('/api/user/profile', {
                headers: {
                    'Accept': 'application/json',
                }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    this.profileData = data;
                    this.profileForm.name = data.user.name;
                    // Atualiza os dados de gamificação globais no App
                    if (data.stats) {
                        this.gamificationData = data.stats;
                    }
                }
            }
        } catch (e) {
            console.error('[Profile] Erro ao buscar perfil:', e);
        } finally {
            this.profileLoading = false;
        }
    },

    async updateProfileInfo() {
        if (this.profileSubmitting) return;

        this.profileSubmitting = true;
        this.profileMessage = null;
        this.profileError = null;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch('/api/user/profile', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ name: this.profileForm.name })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                this.profileMessage = data.message || 'Dados atualizados com sucesso!';
                if (this.profileData && this.profileData.user) {
                    this.profileData.user.name = data.user.name;
                }
            } else {
                this.profileError = data.message || 'Ocorreu um erro ao atualizar os dados.';
            }
        } catch (e) {
            console.error('[Profile] Erro ao atualizar perfil:', e);
            this.profileError = 'Ocorreu uma falha de conexão. Tente novamente.';
        } finally {
            this.profileSubmitting = false;
        }
    },

    async updateUserPassword() {
        if (this.passwordSubmitting) return;

        this.passwordSubmitting = true;
        this.passwordMessage = null;
        this.passwordError = null;
        this.passwordFieldErrors = {};

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch('/api/user/password', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(this.passwordForm)
            });

            const data = await res.json();

            if (res.ok && data.success) {
                this.passwordMessage = data.message || 'Senha alterada com sucesso!';
                this.passwordForm.current_password = '';
                this.passwordForm.password = '';
                this.passwordForm.password_confirmation = '';
            } else {
                this.passwordError = data.message || 'Não foi possível alterar a senha.';
                if (data.errors) {
                    this.passwordFieldErrors = data.errors;
                }
            }
        } catch (e) {
            console.error('[Profile] Erro ao alterar senha:', e);
            this.passwordError = 'Ocorreu uma falha de conexão. Tente novamente.';
        } finally {
            this.passwordSubmitting = false;
        }
    }
});
