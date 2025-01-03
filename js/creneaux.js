document.addEventListener("DOMContentLoaded", function () {
    const etablissementSelect = document.getElementById("etablissement");
    const customEtablissementContainer = document.getElementById("custom-etablissement-container");
    const customEtablissementInput = document.getElementById("custom_etablissement");

    etablissementSelect.addEventListener("change", function () {
        if (etablissementSelect.value === "autre") {
            customEtablissementContainer.style.display = "block";
            customEtablissementInput.required = true;
        } else {
            customEtablissementContainer.style.display = "none";
            customEtablissementInput.required = false;
        }
    });
});
