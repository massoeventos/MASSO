<script setup>
import { form, isLocked, clearFieldError, fieldError } from '../store';
import { t } from '../i18n';
import { useLocation } from '../useLocation';
import FieldError from './FieldError.vue';

// País de residencia: Chile pide región/ciudad (select); otro país, ciudad
// libre. Solo se muestra si el evento lo pide (show_location_fields).
const { regions, cities, loadingRegions, loadingCities, isChile } = useLocation();

function onCountryChange() {
    clearFieldError('country_id');
    form.values.region_id = '';
    form.values.city_id = '';
    form.values.custom_city = '';
}

function onRegionChange() {
    form.values.city_id = '';
}
</script>

<template>
    <div class="col-md-12 form-group">
        <label>{{ t('residenceCountry') }} *</label>
        <select
            v-model="form.values.country_id"
            name="country_id"
            class="form-control"
            :class="{ 'locked-field': isLocked('country_id'), 'is-invalid': fieldError('country_id') }"
            :disabled="isLocked('country_id')"
            required
            @change="onCountryChange"
        >
            <option value="">{{ t('selectCountry') }}</option>
            <option v-for="country in form.countries" :key="country.id" :value="String(country.id)">{{ country.name }}</option>
        </select>
        <input v-if="isLocked('country_id')" type="hidden" name="country_id" :value="form.values.country_id">
        <FieldError name="country_id" />
    </div>

    <template v-if="isChile">
        <div class="col-md-12 form-group">
            <label>{{ t('region') }} *</label>
            <select
                v-model="form.values.region_id"
                name="region_id"
                class="form-control"
                :class="{ 'locked-field': isLocked('region_id') }"
                :disabled="isLocked('region_id') || loadingRegions || !regions.length"
                required
                @change="onRegionChange"
            >
                <option value="">{{ loadingRegions ? t('loading') : t('selectRegion') }}</option>
                <option v-for="region in regions" :key="region.id" :value="region.id">{{ region.name }}</option>
            </select>
            <input v-if="isLocked('region_id')" type="hidden" name="region_id" :value="form.values.region_id">
        </div>

        <div class="col-md-12 form-group">
            <label>{{ t('city') }} *</label>
            <select
                v-model="form.values.city_id"
                name="city_id"
                class="form-control"
                :class="{ 'locked-field': isLocked('city_id'), 'is-invalid': fieldError('city_id') }"
                :disabled="isLocked('city_id') || loadingCities || !cities.length"
                required
                @change="clearFieldError('city_id')"
            >
                <option value="">{{ loadingCities ? t('loading') : t('selectCity') }}</option>
                <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
            </select>
            <input v-if="isLocked('city_id')" type="hidden" name="city_id" :value="form.values.city_id">
            <FieldError name="city_id" />
        </div>
    </template>

    <div v-else-if="form.values.country_id" class="col-md-12 form-group">
        <label>{{ t('city') }} *</label>
        <input
            v-model="form.values.custom_city"
            type="text"
            name="custom_city"
            class="form-control"
            :class="{ 'locked-field': isLocked('custom_city'), 'is-invalid': fieldError('custom_city') }"
            :readonly="isLocked('custom_city')"
            :placeholder="t('enterCity')"
            maxlength="100"
            required
            @input="clearFieldError('custom_city')"
        >
        <FieldError name="custom_city" />
    </div>
</template>
