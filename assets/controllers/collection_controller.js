import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["container", "addButton"];

    connect() {
        this.index = this.containerTarget.children.length;
    }

    add(event) {
        event.preventDefault();
        const prototype = this.containerTarget.dataset.prototype;
        const newForm = prototype.replace(/__name__/g, this.index);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newForm;
        const formElem = tempDiv.firstElementChild;
        this.containerTarget.appendChild(formElem);
        this.index++;
    }

    remove(event) {
        event.preventDefault();
        event.target.closest('.form-group, .mb-3').remove();
    }
}
