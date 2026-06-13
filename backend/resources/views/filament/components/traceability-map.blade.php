@php
    $actualRecord = isset($record) ? $record : (isset($getRecord) && is_callable($getRecord) ? $getRecord() : null);
    $service = new \App\Services\TraceabilityService();
    $networkData = $actualRecord ? $service->getTraceabilityNodes($actualRecord) : ['nodes' => [], 'edges' => []];
    $nodes = $networkData['nodes'] ?? [];
    $edges = $networkData['edges'] ?? [];
@endphp

@if(count($nodes) > 0)
    <div style="margin-top: 2rem; background: #fff; border-radius: 0.75rem; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); overflow: hidden;" class="dark:bg-gray-900 dark:border-white/10">
        <div style="padding: 1.5rem 1.5rem 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;" class="dark:border-white/10">
            <h3 style="font-size: 1rem; font-weight: 600; line-height: 1.5rem; margin: 0; color: #111827;" class="dark:text-white">
                Trazabilidad y Flujo de Documentos (Mapa Mental)
            </h3>
            <p style="font-size: 0.875rem; margin-top: 0.25rem; color: #6b7280;" class="dark:text-gray-400">
                Puedes arrastrar los nodos para organizar la vista a tu gusto.
            </p>
        </div>
        
        <div style="position: relative; width: 100%; height: 400px; background-color: #f9fafb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem;" class="dark:bg-gray-800" 
             x-data="{
                nodesData: {{ json_encode($nodes) }},
                edgesData: {{ json_encode($edges) }},
                initMap() {
                    if (typeof vis === 'undefined') {
                        let script = document.getElementById('vis-network-script');
                        if (!script) {
                            script = document.createElement('script');
                            script.id = 'vis-network-script';
                            script.src = 'https://unpkg.com/vis-network/standalone/umd/vis-network.min.js';
                            document.head.appendChild(script);
                        }
                        script.addEventListener('load', () => this.drawMap());
                        
                        // If it's already loading but not yet defined, poll
                        const checkVis = setInterval(() => {
                            if (typeof vis !== 'undefined') {
                                clearInterval(checkVis);
                                this.drawMap();
                            }
                        }, 200);
                    } else {
                        this.drawMap();
                    }
                },
                drawMap() {
                    if (!this.$refs.networkContainer) return;
                    
                    const container = this.$refs.networkContainer;
                    const data = {
                        nodes: new vis.DataSet(this.nodesData),
                        edges: new vis.DataSet(this.edgesData)
                    };
                    
                    const options = {
                        layout: {
                            hierarchical: {
                                enabled: true,
                                direction: 'UD',
                                sortMethod: 'directed',
                                levelSeparation: 150,
                                nodeSpacing: 250
                            }
                        },
                        physics: {
                            enabled: false
                        },
                        interaction: {
                            dragNodes: true,
                            dragView: true,
                            zoomView: true
                        },
                        edges: {
                            smooth: {
                                type: 'cubicBezier',
                                forceDirection: 'vertical',
                                roundness: 0.4
                            },
                            width: 2
                        }
                    };
                    
                    new vis.Network(container, data, options);
                }
             }" 
             x-init="initMap()">
            
            <div x-ref="networkContainer" style="width: 100%; height: 100%;"></div>

        </div>
    </div>
@else
    <div style="padding: 1.5rem; font-size: 0.875rem; color: #6b7280; text-align: center; border: 1px solid #e5e7eb; border-radius: 0.5rem; margin-top: 2rem;" class="dark:border-gray-700 dark:text-gray-400">
        No se encontraron documentos relacionados en la trazabilidad.
    </div>
@endif
