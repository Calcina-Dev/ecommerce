@php
    $actualRecord = isset($record) ? $record : (isset($getRecord) && is_callable($getRecord) ? $getRecord() : null);
    $service = new \App\Services\TraceabilityService();
    $networkData = $actualRecord ? $service->getTraceabilityNodes($actualRecord) : ['nodes' => [], 'edges' => []];
    $nodes = $networkData['nodes'] ?? [];
    $edges = $networkData['edges'] ?? [];
@endphp

@if(count($nodes) > 0)
    <div style="position: relative; width: 100%; height: 500px; border-radius: 0.5rem; overflow: hidden; background-color: #fafafa; border: 1px solid #f3f4f6;" class="dark:bg-gray-900/50 dark:border-white/5" 
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
                            levelSeparation: 120,
                            nodeSpacing: 180
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
                        width: 2,
                        color: { color: '#9ca3af', highlight: '#3b82f6' }
                    }
                };
                
                new vis.Network(container, data, options);
            }
         }" 
         x-init="initMap()">
        
        <div x-ref="networkContainer" style="width: 100%; height: 100%;"></div>

    </div>
@else
    <div style="padding: 1.5rem; font-size: 0.875rem; color: #6b7280; text-align: center; border: 1px dashed #e5e7eb; border-radius: 0.5rem;" class="dark:border-white/10 dark:text-gray-400">
        No se encontraron documentos relacionados.
    </div>
@endif
