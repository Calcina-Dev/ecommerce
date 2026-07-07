<x-filament-widgets::widget>
    <x-filament::section>
        <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white mb-4">
            Mapa de Envíos Nacionales
        </h2>

        <div
            x-data="{
                mapData: @js($mapData),
                map: null,
                geojsonLayer: null,
                info: null,
                maxOrders: 0,
                
                init() {
                    if (typeof L === 'undefined') {
                        // Load CSS
                        const link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                        document.head.appendChild(link);

                        // Load JS
                        const script = document.createElement('script');
                        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                        script.onload = () => this.initMap();
                        document.head.appendChild(script);
                    } else {
                        this.initMap();
                    }
                },

                updateMapStyles() {
                    this.maxOrders = 0;
                    for (const city in this.mapData) {
                        if (this.mapData[city].orders > this.maxOrders) {
                            this.maxOrders = this.mapData[city].orders;
                        }
                    }
                    if (this.geojsonLayer) {
                        this.geojsonLayer.eachLayer((layer) => {
                            this.geojsonLayer.resetStyle(layer);
                        });
                    }
                },

                initMap() {
                    // Check if map container exists and is not already initialized
                    if (!this.$refs.mapElement || this.map !== null) return;
                    
                    this.map = L.map(this.$refs.mapElement).setView([-9.19, -75.0152], 5);

                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        subdomains: 'abcd',
                        maxZoom: 20
                    }).addTo(this.map);

                    this.updateMapStyles();

                    // Tooltip Info Control
                    this.info = L.control();
                    this.info.onAdd = function () {
                        this._div = L.DomUtil.create('div', 'leaflet-info-box');
                        this._div.style.padding = '6px 8px';
                        this._div.style.font = '14px/16px Arial, Helvetica, sans-serif';
                        this._div.style.background = 'rgba(255,255,255,0.9)';
                        this._div.style.boxShadow = '0 0 15px rgba(0,0,0,0.2)';
                        this._div.style.borderRadius = '5px';
                        this._div.style.color = '#111';
                        this.update();
                        return this._div;
                    };

                    const self = this;
                    this.info.update = function (props) {
                        if (props) {
                            const depName = props.NOMBDEP ? props.NOMBDEP.toUpperCase() : '';
                            const data = self.mapData[depName];
                            if (data) {
                                this._div.innerHTML = `<h4 style='margin:0 0 5px;color:#777;'>${props.NOMBDEP}</h4><b>${data.orders}</b> despachos<br>Ingresos: PEN ${parseFloat(data.revenue).toFixed(2)}`;
                            } else {
                                this._div.innerHTML = `<h4 style='margin:0 0 5px;color:#777;'>${props.NOMBDEP}</h4>Sin despachos registrados`;
                            }
                        } else {
                            this._div.innerHTML = `<h4 style='margin:0 0 5px;color:#777;'>Despachos</h4>Pasa el mouse sobre un departamento`;
                        }
                    };
                    this.info.addTo(this.map);

                    fetch('/peru_departamentos.json')
                        .then(response => response.json())
                        .then(data => {
                            this.geojsonLayer = L.geoJson(data, {
                                style: (feature) => this.styleFeature(feature),
                                onEachFeature: (feature, layer) => this.onEachFeature(feature, layer)
                            }).addTo(this.map);
                        });
                },

                getColor(d) {
                    return d > (this.maxOrders * 0.8) ? '#d97706' : 
                           d > (this.maxOrders * 0.5) ? '#f59e0b' : 
                           d > (this.maxOrders * 0.2) ? '#fbbf24' : 
                           d > 0                      ? '#fde68a' : 
                                                        '#f3f4f6';
                },

                styleFeature(feature) {
                    const depName = feature.properties.NOMBDEP ? feature.properties.NOMBDEP.toUpperCase() : '';
                    let orders = this.mapData[depName] ? this.mapData[depName].orders : 0;
                    
                    return {
                        fillColor: this.getColor(orders),
                        weight: 1,
                        opacity: 1,
                        color: '#9ca3af',
                        dashArray: '3',
                        fillOpacity: 0.7
                    };
                },

                onEachFeature(feature, layer) {
                    layer.on({
                        mouseover: (e) => {
                            const l = e.target;
                            l.setStyle({ weight: 2, color: '#666', dashArray: '', fillOpacity: 0.9 });
                            l.bringToFront();
                            this.info.update(l.feature.properties);
                        },
                        mouseout: (e) => {
                            this.geojsonLayer.resetStyle(e.target);
                            this.info.update();
                        }
                    });
                }
            }"
            x-effect="
                mapData = @js($mapData);
                updateMapStyles();
            "
        >
            <div x-ref="mapElement" wire:ignore style="height: 400px; width: 100%; border-radius: 0.5rem; z-index: 1;"></div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
