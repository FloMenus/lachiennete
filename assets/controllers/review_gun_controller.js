import { Controller } from '@hotwired/stimulus';

/* Braque un pistolet sur l'utilisateur quand il sélectionne la note 1 étoile (page d'avis).
   L'arme se range d'elle-même dès qu'une note plus clémente est choisie. */
export default class extends Controller {
    static targets = ['holster'];

    connect() {
        this.update();
    }

    update() {
        const armed = this.element.querySelector('input[type="radio"][value="1"]')?.checked ?? false;
        this.holsterTarget.classList.toggle('is-armed', armed);
    }
}
