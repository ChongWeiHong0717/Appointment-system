import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('bookingFlow', (config) => ({
    step: config.initialStep ?? 1,
    categories: config.categories,
    selectedCategoryId: config.initialCategory ? Number(config.initialCategory) : null,
    selectedServiceId: config.initialService ? Number(config.initialService) : null,
    appointmentDate: config.initialDate ?? '',
    selectedTime: config.initialTime ?? '',
    customerName: config.customerName ?? '',
    customerPhone: config.customerPhone ?? '',
    customerEmail: config.customerEmail ?? '',
    customerNotes: config.customerNotes ?? '',
    slots: [],
    loadingSlots: false,
    slotError: '',

    get selectedCategory() {
        return this.categories.find((category) => category.id === this.selectedCategoryId);
    },

    get services() {
        return this.selectedCategory?.services ?? [];
    },

    get selectedService() {
        return this.categories.flatMap((category) => category.services)
            .find((service) => service.id === this.selectedServiceId);
    },

    chooseCategory(id) {
        if (this.selectedCategoryId !== id) {
            this.selectedServiceId = null;
            this.selectedTime = '';
            this.slots = [];
        }
        this.selectedCategoryId = id;
    },

    chooseService(id) {
        this.selectedServiceId = id;
        this.selectedTime = '';
        this.slots = [];
    },

    async loadSlots() {
        this.selectedTime = '';
        this.slots = [];
        this.slotError = '';

        if (!this.appointmentDate || !this.selectedServiceId) return;

        this.loadingSlots = true;
        try {
            const url = new URL(config.slotsUrl, window.location.origin);
            url.searchParams.set('service_id', this.selectedServiceId);
            url.searchParams.set('date', this.appointmentDate);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });

            if (!response.ok) throw new Error('Unable to load appointment times.');

            const payload = await response.json();
            this.slots = payload.slots;
        } catch (error) {
            this.slotError = error.message;
        } finally {
            this.loadingSlots = false;
        }
    },

    next(nextStep) {
        this.step = nextStep;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if (nextStep === 4 && this.slots.length === 0) this.loadSlots();
    },
}));

Alpine.start();
