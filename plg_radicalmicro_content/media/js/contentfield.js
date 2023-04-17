/*
 * @package   pkg_radicalmicro
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-contentfield-container]').forEach((container) => {
		let select = container.querySelector('select');
		let input  = container.querySelector('input');

		select.addEventListener('change', function (event) {
			let value = event.target.value;

			input.type = 'hidden';

			if (value === '_noselect_')
			{
				input.value = '';
			} else if (value === '_custom_') {
				input.type = 'text';
				input.value = '';
			} else {
				input.value = event.target.value;
			}
		});

		if (!input.value) {
			select.value = '_noselect_';
			input.type = 'hidden';
		} else {
			select.value = select.getAttribute('data-value');
		}
    });
});