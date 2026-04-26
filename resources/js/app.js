import './bootstrap';
import './echo';
import './cart';
import './wishlist';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

const initPasswordToggles = () => {
	document.querySelectorAll('[data-password-toggle]').forEach((toggleButton) => {
		if (toggleButton.dataset.bound === '1') {
			return;
		}

		toggleButton.dataset.bound = '1';
		toggleButton.addEventListener('click', () => {
			const targetId = toggleButton.getAttribute('data-target');
			const passwordInput = targetId ? document.getElementById(targetId) : null;

			if (!passwordInput) {
				return;
			}

			const isPasswordHidden = passwordInput.type === 'password';
			passwordInput.type = isPasswordHidden ? 'text' : 'password';
			toggleButton.textContent = isPasswordHidden ? 'Hide' : 'Show';
			toggleButton.setAttribute('aria-label', isPasswordHidden ? 'Hide password' : 'Show password');
		});
	});
};

const initRegisterCityLoader = () => {
	const provinceSelect = document.getElementById('province_id');
	const citySelect = document.getElementById('city_id');

	if (!provinceSelect || !citySelect) {
		return;
	}

	const loadingLabel = document.querySelector('[x-show="loadingCities"]');
	const oldProvince = provinceSelect.dataset.oldProvince || '';
	const oldCity = citySelect.dataset.oldCity || '';

	const setLoading = (loading) => {
		citySelect.disabled = loading || !provinceSelect.value;
		if (loadingLabel) {
			loadingLabel.style.display = loading ? 'inline' : 'none';
		}
	};

	const resetCityOptions = (placeholder = 'Choose province first') => {
		citySelect.innerHTML = '';
		const option = document.createElement('option');
		option.value = '';
		option.textContent = placeholder;
		citySelect.appendChild(option);
	};

	const populateCities = (cities, selectedCity = '') => {
		resetCityOptions('Select city');

		cities.forEach((city) => {
			const option = document.createElement('option');
			option.value = String(city.id);
			option.textContent = city.name;

			if (selectedCity && String(city.id) === String(selectedCity)) {
				option.selected = true;
			}

			citySelect.appendChild(option);
		});
	};

	const fetchCities = async (provinceId, selectedCity = '') => {
		if (!provinceId) {
			resetCityOptions();
			setLoading(false);
			return;
		}

		setLoading(true);

		try {
			const response = await fetch(`/api/cities/${provinceId}`, {
				headers: { Accept: 'application/json' },
			});

			if (!response.ok) {
				throw new Error('Failed to load cities');
			}

			const payload = await response.json();
			const cities = Array.isArray(payload) ? payload : [];
			populateCities(cities, selectedCity);
			citySelect.disabled = false;
		} catch (error) {
			resetCityOptions('No cities available');
			citySelect.disabled = true;
		} finally {
			setLoading(false);
		}
	};

	provinceSelect.addEventListener('change', () => {
		fetchCities(provinceSelect.value, '');
	});

	if (oldProvince) {
		provinceSelect.value = oldProvince;
		fetchCities(oldProvince, oldCity);
	} else {
		resetCityOptions();
		citySelect.disabled = true;
		setLoading(false);
	}
};

document.addEventListener('DOMContentLoaded', () => {
	initPasswordToggles();
	initRegisterCityLoader();
});