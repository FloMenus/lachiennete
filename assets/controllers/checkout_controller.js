import { Controller } from '@hotwired/stimulus';

/* Affiche le formulaire de nouvelle adresse uniquement quand "Livrer ailleurs" est sélectionné. */
export default class extends Controller {
    static targets = ['newAddress'];

    toggle(event) {
        this.newAddressTarget.classList.toggle('hidden', event.target.value !== 'new');
    }
}
