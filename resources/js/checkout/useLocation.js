import { ref, computed, watch } from 'vue';
import { form } from './store';

// Opciones de región/ciudad (LocationController: /get-regions/{país},
// /get-cities/{región}, responden { id: nombre }). Solo cargan listas: los
// valores elegidos viven en form.values y se limpian en los @change del
// componente, así el autocompletado puede fijar país/región/ciudad de una vez
// sin que esto los borre.
export function useLocation() {
    const regions = ref([]);
    const cities = ref([]);
    const loadingRegions = ref(false);
    const loadingCities = ref(false);

    const isChile = computed(() => String(form.values.country_id) === String(form.chileId));

    let regionsRequest = 0;
    let citiesRequest = 0;

    async function fetchOptions(url) {
        const res = await fetch(`${url}?lang=${form.lang}`, { headers: { Accept: 'application/json' } });
        const data = await res.json();
        return Object.entries(data || {}).map(([id, name]) => ({ id: String(id), name }));
    }

    watch(
        isChile,
        async (chile) => {
            const request = ++regionsRequest;
            regions.value = [];
            if (!chile) return;
            loadingRegions.value = true;
            try {
                const list = await fetchOptions(`${form.urls.regions}/${form.chileId}`);
                if (request === regionsRequest) regions.value = list;
            } catch (e) {
                console.error('[checkout] error cargando regiones', e);
            } finally {
                if (request === regionsRequest) loadingRegions.value = false;
            }
        },
        { immediate: true }
    );

    watch(
        () => (isChile.value ? form.values.region_id : ''),
        async (regionId) => {
            const request = ++citiesRequest;
            cities.value = [];
            if (!regionId) return;
            loadingCities.value = true;
            try {
                const list = await fetchOptions(`${form.urls.cities}/${regionId}`);
                if (request === citiesRequest) cities.value = list;
            } catch (e) {
                console.error('[checkout] error cargando ciudades', e);
            } finally {
                if (request === citiesRequest) loadingCities.value = false;
            }
        },
        { immediate: true }
    );

    return { regions, cities, loadingRegions, loadingCities, isChile };
}
