// Base de datos de modelos por marca
const vehicleModels = {
    "Toyota": [
        "Corolla", "Camry", "RAV4", "Hilux", "Yaris", "Prado", 
        "4Runner", "Tacoma", "Tundra", "Highlander", "Sienna", 
        "Avalon", "Prius", "CH-R", "86", "Supra"
    ],
    "Honda": [
        "Civic", "Accord", "CR-V", "HR-V", "Pilot", "Odyssey",
        "Fit", "Ridgeline", "Passport", "Insight", "Clarity"
    ],
    "Nissan": [
        "Sentra", "Altima", "Versa", "Maxima", "Patrol", "Frontier",
        "Kicks", "Rogue", "Murano", "Pathfinder", "Armada", "Titan",
        "370Z", "GT-R", "Leaf"
    ],
    "Mazda": [
        "Mazda2", "Mazda3", "Mazda6", "CX-3", "CX-5", "CX-9",
        "CX-30", "MX-5 Miata", "CX-50"
    ],
    "Hyundai": [
        "Accent", "Elantra", "Sonata", "Tucson", "Santa Fe", "Kona",
        "Palisade", "Venue", "Ioniq", "Veloster"
    ],
    "Kia": [
        "Rio", "Forte", "K5", "Sportage", "Sorento", "Telluride",
        "Seltos", "Soul", "Stinger", "Carnival", "Niro"
    ],
    "Chevrolet": [
        "Spark", "Aveo", "Sonic", "Cruze", "Malibu", "Impala",
        "Camaro", "Corvette", "Trax", "Equinox", "Traverse", "Tahoe",
        "Suburban", "Silverado", "Colorado", "Blazer"
    ],
    "Ford": [
        "Fiesta", "Focus", "Fusion", "Mustang", "EcoSport", "Escape",
        "Edge", "Explorer", "Expedition", "F-150", "Ranger", "Bronco"
    ],
    "Volkswagen": [
        "Gol", "Polo", "Jetta", "Passat", "Golf", "Tiguan", "Touareg",
        "Atlas", "Arteon", "Beetle", "T-Cross"
    ],
    "Mitsubishi": [
        "Mirage", "Lancer", "Eclipse Cross", "Outlander", "Outlander Sport",
        "Pajero", "L200", "Montero", "ASX"
    ],
    "Suzuki": [
        "Alto", "Swift", "Baleno", "Ciaz", "Vitara", "S-Cross",
        "Ertiga", "Jimny", "Grand Vitara"
    ],
    "Subaru": [
        "Impreza", "Legacy", "Outback", "Forester", "Crosstrek",
        "Ascent", "WRX", "BRZ"
    ],
    "Mercedes-Benz": [
        "Clase A", "Clase C", "Clase E", "Clase S", "GLA", "GLB",
        "GLC", "GLE", "GLS", "Clase G", "AMG GT", "EQC"
    ],
    "BMW": [
        "Serie 1", "Serie 2", "Serie 3", "Serie 4", "Serie 5", "Serie 7",
        "X1", "X2", "X3", "X4", "X5", "X6", "X7", "Z4", "i3", "i8"
    ],
    "Audi": [
        "A1", "A3", "A4", "A5", "A6", "A7", "A8", "Q2", "Q3", "Q5",
        "Q7", "Q8", "TT", "R8", "e-tron"
    ],
    "Otra": [
        "Otro modelo"
    ]
};

// Función para actualizar los modelos según la marca seleccionada
function updateModels() {
    const brandSelect = document.getElementById('brand_select');
    const modelSelect = document.getElementById('model_select');
    
    const selectedBrand = brandSelect.value;
    
    // Limpiar opciones actuales
    modelSelect.innerHTML = '<option value="">Seleccione un modelo</option>';
    
    if (selectedBrand && vehicleModels[selectedBrand]) {
        // Habilitar el select de modelos
        modelSelect.disabled = false;
        
        // Agregar los modelos de la marca seleccionada
        vehicleModels[selectedBrand].forEach(model => {
            const option = document.createElement('option');
            option.value = model;
            option.textContent = model;
            modelSelect.appendChild(option);
        });
        
        // Si la marca es "Otra", permitir input personalizado
        if (selectedBrand === "Otra") {
            const option = document.createElement('option');
            option.value = "custom";
            option.textContent = "Escribir modelo...";
            modelSelect.appendChild(option);
        }
    } else {
        // Deshabilitar el select de modelos
        modelSelect.disabled = true;
    }
}

// Agregar evento al select de marca
document.addEventListener('DOMContentLoaded', function() {
    const brandSelect = document.getElementById('brand_select');
    if (brandSelect) {
        brandSelect.addEventListener('change', updateModels);
    }
    
    // Manejar modelo personalizado
    const modelSelect = document.getElementById('model_select');
    if (modelSelect) {
        modelSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                const customModel = prompt('Ingrese el modelo del vehículo:');
                if (customModel && customModel.trim()) {
                    // Crear una opción personalizada
                    const option = document.createElement('option');
                    option.value = customModel.trim();
                    option.textContent = customModel.trim();
                    option.selected = true;
                    this.insertBefore(option, this.firstChild.nextSibling);
                } else {
                    this.value = '';
                }
            }
        });
    }
});