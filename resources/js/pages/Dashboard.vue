<script >
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
  import 'ol/ol.css';
  import 'ol-ext/dist/ol-ext.css';
  import Map from 'ol/Map';
  import View from 'ol/View';
  import { Vector as VectorLayer } from 'ol/layer';
  import VectorSource from 'ol/source/Vector';
  import GeoJSON from 'ol/format/GeoJSON';
  import { Fill, Stroke, Style, Circle, Text } from 'ol/style';
  import { fromLonLat } from 'ol/proj';
  import {geojson, nigeria} from './Utils';
  import Heatmap from 'ol/layer/Heatmap';
  import Feature from 'ol/Feature';
  import Point from 'ol/geom/Point';
  import Overlay from 'ol/Overlay';
  import {abia} from "./Utils/states/abia";
  import {adamawa} from "./Utils/states/adamawa";
  import {akwa_ibom} from "./Utils/states/akwa_ibom";
  import {anambra} from "./Utils/states/anambra";
  import {bauchi} from "./Utils/states/bauchi";
  import {bayelsa} from "./Utils/states/bayelsa";
  import {benue} from "./Utils/states/benue";
  import {borno} from "./Utils/states/borno";
  import {cross_river} from "./Utils/states/cross_river";
  import {delta} from "./Utils/states/delta";
  import {ebonyi} from "./Utils/states/ebonyi";
  import {edo} from "./Utils/states/edo";
  import {ekiti} from "./Utils/states/ekiti";
  import {enugu} from "./Utils/states/enugu";
  import {gombe} from "./Utils/states/gombe";
  import {imo} from "./Utils/states/imo";
  import {jigawa} from "./Utils/states/jigawa";
  import {kaduna} from "./Utils/states/kaduna";
  import {kano} from "./Utils/states/kano";
  import {katsina} from "./Utils/states/katsina";
  import {kebbi} from "./Utils/states/kebbi";
  import {kogi} from "./Utils/states/kogi";
  import {kwara} from "./Utils/states/kwara";
  import {lagos} from "./Utils/states/lagos";
  import {nasarawa} from "./Utils/states/nasarawa";
  import {niger} from "./Utils/states/niger";
  import {ogun} from "./Utils/states/ogun";
  import {ondo} from "./Utils/states/ondo";
  import {osun} from "./Utils/states/osun";
  import {oyo} from "./Utils/states/oyo";
  import {plateau} from "./Utils/states/plateau";
  import {rivers} from "./Utils/states/rivers";
  import {sokoto} from "./Utils/states/sokoto";
  import {taraba} from "./Utils/states/taraba";
  import {yobe} from "./Utils/states/yobe";
  import {zamfara} from "./Utils/states/zamfara";
  import {fct} from "./Utils/states/fct";


  const stateCoordinates = { 
    nigeria: { center: [8.6753, 9.0820], zoom: 6 },
    abia: { center: [7.4867, 5.5320], zoom: 9 },
    adamawa: { center: [13.2543, 9.3265], zoom: 7 },
    akwaibom: { center: [7.8536, 4.9057], zoom: 9 },
    anambra: { center: [7.0697, 6.2109], zoom: 9 },
    bauchi: { center: [10.3010, 10.3764], zoom: 7 },
    bayelsa: { center: [6.0696, 4.7719], zoom: 9 },
    benue: { center: [8.7404, 7.3369], zoom: 8 },
    borno: { center: [13.1514, 11.8333], zoom: 7 },
    crossriver: { center: [8.5988, 5.8702], zoom: 8 },
    delta: { center: [6.0025, 5.5320], zoom: 8 },
    ebonyi: { center: [8.0833, 6.2463], zoom: 9 },
    edo: { center: [6.2676, 6.6342], zoom: 8 },
    ekiti: { center: [5.3107, 7.6233], zoom: 9 },
    enugu: { center: [7.5464, 6.4584], zoom: 9 },
    gombe: { center: [11.1714, 10.2897], zoom: 8 },
    imo: { center: [7.0266, 5.4920], zoom: 9 },
    jigawa: { center: [9.5618, 12.2280], zoom: 8 },
    kaduna: { center: [7.7099, 10.5105], zoom: 8 },
    kano: { center: [8.5167, 11.9965], zoom: 8 },
    katsina: { center: [7.6229, 12.9855], zoom: 8 },
    kebbi: { center: [4.1999, 12.0333], zoom: 8 },
    kogi: { center: [6.7269, 7.8004], zoom: 8 },
    kwara: { center: [4.5500, 8.9669], zoom: 8 },
    lagos: { center: [3.3792, 6.5244], zoom: 10 },
    nasarawa: { center: [8.1477, 8.4998], zoom: 8 },
    niger: { center: [5.7880, 9.9309], zoom: 8 },
    ogun: { center: [3.4737, 7.1557], zoom: 9 },
    ondo: { center: [5.1477, 7.2423], zoom: 9 },
    osun: { center: [4.5200, 7.6295], zoom: 9 },
    oyo: { center: [3.9300, 8.1574], zoom: 8 },
    plateau: { center: [9.2182, 9.5179], zoom: 8 },
    rivers: { center: [6.9112, 4.8396], zoom: 9 },
    sokoto: { center: [5.2433, 13.0059], zoom: 8 },
    taraba: { center: [9.6570, 8.1449], zoom: 8 },
    yobe: { center: [11.7489, 11.9669], zoom: 8 },
    zamfara: { center: [6.2236, 12.1222], zoom: 8 },
    fct: { center: [7.4893, 9.0579], zoom: 9 } // Federal Capital Territory
  };
  

  export default {
    name: 'NigeriaMap',
    components: {
      AppLayout,
      Head,
        PlaceholderPattern
    },
    props: {
      region: {
        type: String,
        default: 'nigeria',
        // validator: value => {
        //   return Object.keys(stateCoordinates).includes(value.toLowerCase());
        // }
      },
      heatData: {
        type: Array,
        default: () => [
        //   {lat: 9.2179, lng: 7.1898, change: 1231}, // Lagos Island
        //   {lat: 9.6522, lng: 6.5261, change: 11},   // Orozo
        //   {lat: 9.5836, lng: 6.5463, change: 322},
        //   {lat: 9.0493, lng: 6.5797, change: 322},
        ]
      },
      visualizationType: {
        type: String,
        default: 'dots', // 'heatmap' or 'dots'
        validator: value => ['heatmap', 'dots'].includes(value)
      }
    },
    data() {
      return {
        breadcrumbs:  [
            {
                title: 'Dashboard',
                href: '/dashboard',
            },
        ],
        map: null,
        states: {
            abia,
            adamawa,
            akwaibom:akwa_ibom,
            anambra,
            bauchi,
            bayelsa,
            benue,
            borno,
            crossriver:cross_river,
            delta,
            ebonyi,
            edo,
            ekiti,
            enugu,
            gombe,
            imo,
            jigawa,
            kaduna,
            kano,
            katsina,
            kebbi,
            kogi,
            kwara,
            lagos,
            nasarawa,
            niger,
            ogun,
            ondo,
            osun,
            oyo,
            plateau,
            rivers,
            sokoto,
            taraba,
            yobe,
            zamfara,
            fct,
        },

        heatmapLayer: null,
        dotsLayer: null,
        tooltipOverlay: null,
        hoverVectorLayer: null,
        currentFeature: null,
        info:null,
      };
    },
    mounted() {
      this.loadMap();
      this.info = document.getElementById('mapInfo');
    },
    watch: {
      region(newVal) {
        if (this.map) {
          this.map.setTarget(undefined);
        }
        this.loadMap();
      },
      heatData: {
        handler(newData) {
          this.updateVisualization(newData);
        },
        deep: true
      },
      visualizationType() {
        this.updateVisualization(this.heatData);
      }
    },
    methods: {
      createHeatmapLayer(data) {
        const maxchange = Math.max(...data.map(item => item.change || 1), 1);
        const features = data.map(item => {
          const point = new Feature({
            geometry: new Point(fromLonLat([item.lng, item.lat])),
          });
          point.set('weight', item.change / maxchange);
          return point;
        });
  
        const vectorSource = new VectorSource({ features });
  
        return new Heatmap({
          source: vectorSource,
          radius: 25,
          blur: 15,
          weight: feature => feature.get('weight'),
          gradient: ['#00f', '#0ff', '#0f0', '#ff0', '#f00']
        });
      },
      
      createDotsLayer(data) {
        const maxchange = Math.max(...data.map(item => item.change || 1), 1);
        const features = data.map(item => {
          const feature = new Feature({
            geometry: new Point(fromLonLat([item.lng, item.lat])),
          });
          feature.set('change', item.change);
          return feature;
        });
  
        const vectorSource = new VectorSource({ features });
  
        return new VectorLayer({
          source: vectorSource,
          style: feature => {
            const change = feature.get('change');
            //const radius = Math.min(10 + (change / maxchange) * 20, 20); // Dynamic radius based on change
            const radius =5; // Dynamic radius based on change
            
            return new Style({
              image: new Circle({
                radius: radius,
                fill: new Fill({
                  color: `rgba(181, 81, 18, ${0.5 + (change / maxchange) * 0.5})`
                }),
                // stroke: new Stroke({
                //   color: 'rgba(0, 0, 0, 0.7)',
                //   width: 1
                // })
              }),
              text: new Text({
               // text: change.toString(),
                fill: new Fill({
                  color: '#fff'
                }),
                font: 'bold 12px sans-serif'
              })
            });
          }
        });
      },
      assignchangesToStates(vectorSource) {
        const features = vectorSource.getFeatures();
        
        // Reset changes
        features.forEach(f => f.set('change', 0));
        
        // Sum changes for each state
        this.heatData.forEach(dataPoint => {
            const point = fromLonLat([dataPoint.lng, dataPoint.lat]);
            
            // Find which state contains this point
            const containingFeature = features.find(feature => {
            try {
                return feature.getGeometry().intersectsCoordinate(point);
            } catch (e) {
                console.error('Error checking intersection:', e);
                return false;
            }
            });
            
            if (containingFeature) {
            const currentchange = containingFeature.get('change') || 0;
            containingFeature.set('change', currentchange + (dataPoint.change || 0));
            }
        });
        },
    //   assignchangesToStates(vectorSource) {
    //     const features = vectorSource.getFeatures();
        
    //     // Reset changes
    //     features.forEach(f => f.set('change', 0));
        
    //     // Sum changes for each state
    //     this.heatData.forEach(dataPoint => {
    //     const point = fromLonLat([dataPoint.lng, dataPoint.lat]);
        
    //     // Find which state contains this point
    //     const containingFeature = features.find(feature => 
    //         feature.getGeometry().intersectsCoordinate(point)
    //     );
        
    //     if (containingFeature) {
    //         const currentchange = containingFeature.get('change') || 0;
    //         containingFeature.set('change', currentchange + (dataPoint.change || 0));
    //     }
    //     });
    // },
    displayFeatureInfo(pixel, feature) {
        // const feature = target.closest('.ol-control')
        //     ? undefined
        //     : this.map.forEachFeatureAtPixel(pixel, function (feature) {
        //         return feature;
        //     });
        if (feature) {
            this.info.style.left = pixel[0] + 'px';
            this.info.style.top = pixel[1] + 'px';
            if (feature !== this.currentFeature) {
                this.info.style.visibility = 'visible';
                this.info.style.cursor = 'pointer';
                const stateName = feature.get('NAME_2');
                const change = feature.get('change') || 0;
                this.info.innerHTML = `
                    <div class="tw-font-bold">${stateName}</div>
                    <div class="tw-text-sm">Climate Change: ${change}</div>
                    `;
                //this.info.innerText = feature.get('NAME_1');
            }
        } else {
            this.info.style.visibility = 'hidden';
        }
        this.currentFeature = feature;
    },
    createHoverLayer(data) {
        const features = data.map(item => {
          const feature = new Feature({
            geometry: new Point(fromLonLat([item.lng, item.lat])),
          });
          feature.set('change', item.change);
          return feature;
        });
  
        const vectorSource = new VectorSource({ features });
  
        return new VectorLayer({
          source: vectorSource,
          style: new Style({ fill: new Fill({ color: 'rgba(0,0,0,0)' }) }), // Invisible
        });
      },
      
      updateVisualization(data) {
        // Remove existing layers
        if (this.heatmapLayer) {
          this.map.removeLayer(this.heatmapLayer);
          this.heatmapLayer = null;
        }
        if (this.dotsLayer) {
          this.map.removeLayer(this.dotsLayer);
          this.dotsLayer = null;
        }
        
        // Update hover layer (used for tooltips)
        if (this.hoverVectorLayer) {
          this.map.removeLayer(this.hoverVectorLayer);
        }
        this.hoverVectorLayer = this.createHoverLayer(data);
        this.map?.addLayer(this.hoverVectorLayer);
        
        if(this.map){
                    const vectorLayer = this.map.getLayers().getArray().find(layer => 
                    layer instanceof VectorLayer && layer !== this.dotsLayer && layer !== this.heatmapLayer
                );
                
                if (vectorLayer) {
                    this.assignchangesToStates(vectorLayer.getSource());
                }
                
            }
        // Add the selected visualization
        if (this.visualizationType === 'heatmap') {
          this.heatmapLayer = this.createHeatmapLayer(data);
          this.map.addLayer(this.heatmapLayer);
        } else {
          this.dotsLayer = this.createDotsLayer(data);
          this.map?.addLayer(this.dotsLayer);
        }
        
        // Bring hover layer to top
        this.hoverVectorLayer.setZIndex(100);
      },
      
      async loadMap() {
        try {
            if(!this.region) return;
          const stateKey = this.region?.toLowerCase();
          const { center, zoom } = stateCoordinates[stateKey.replace(' ','')];
        
          const geoJson =  this.states?.[stateKey.replace(' ','_')]
          const vectorSource = new VectorSource({
            features: new GeoJSON().readFeatures(stateKey == 'nigeria'?nigeria:geoJson, {
              featureProjection: 'EPSG:3857',
            }),
          });
  
          const vectorLayer = new VectorLayer({
            source: vectorSource,
            style: new Style({
              stroke: new Stroke({ color: '#6995', width: 1.5 }),
              fill: new Fill({ color: 'rgba(0, 150, 0, 0.3)' }),
            }),
          });

             this.assignchangesToStates(vectorSource);
  
          this.map = new Map({
            target: this.$refs.mapContainer,
            layers: [vectorLayer],
            view: new View({
              center: fromLonLat(center),
              zoom: zoom,
            }),
          });
  
          // Add the initial visualization
          this.updateVisualization(this.heatData);
  
          // Auto zoom to bounds with padding
          this.map.getView().fit(vectorSource.getExtent(), {
            padding: [50, 50, 50, 50],
            duration: 1000,
          });
          
          
          // Add this in your loadMap method after creating the map
        let hoveredFeature = null;
        // this.map.on('click', (evt) => {
        //     alert()
        // });
        // this.map.on('pointermove',  (evt) =>{
        // if (evt.dragging) {
        //     this.info.style.visibility = 'hidden';
        //     this.currentFeature = undefined;
        //     return;
        // }
        // //const feature = this.map.forEachFeatureAtPixel(evt.pixel, (f) => f);
        // const pixel = this.map.getEventPixel(evt.originalEvent);
        // const feature = this.map.forEachFeatureAtPixel(pixel, (feature, layer) => {
        // return layer === this.dotsLayer ? null : feature;
        // });

        // this.displayFeatureInfo(evt.pixel,feature);
        // });

        this.map.on('pointermove', (evt) => {
            if (evt.dragging) {
                this.info.style.visibility = 'hidden';
                this.currentFeature = undefined;
                return;
            }
            
            // Get the state feature at this pixel (ignore dots/heatmap layers)
            const feature = this.map.forEachFeatureAtPixel(evt.pixel, (f, layer) => {
                return layer !== this.dotsLayer && layer !== this.heatmapLayer ? f : null;
            });
            
            this.displayFeatureInfo(evt.pixel, feature);
            
            // Change cursor style
            this.map.getTargetElement().style.cursor = feature ? 'pointer' : '';
            });

        this.map.getTargetElement().addEventListener('pointerleave', function () {
        this.currentFeature = undefined;
        if(this.info){
            this.info.style.visibility = 'hidden';
        }
        });

        // Change cursor style when hovering over features
        this.map.on('click', (evt) => {
        
        const feature = this.map.forEachFeatureAtPixel(evt.pixel, (f) => f);
        if(feature?.get('admin1Pcod')){
            this.$emit('feature-clicked', {name:feature.get('NAME_1'),id:Number(feature.get('admin1Pcod').replace('NG', ''))});
        }

        if (feature) {
            this.map.getViewport().style.cursor = 'pointer';
            // Highlight the hovered feature
            if (hoveredFeature !== feature) {
            if (hoveredFeature) {
                hoveredFeature.setStyle(undefined); // Reset previous hover style
            }
            hoveredFeature = feature;
            feature.setStyle(new Style({
                stroke: new Stroke({ color: 'blue', width: 3 }),
                fill: new Fill({ color: 'rgba(0, 0, 255, 0.3)' }),
            }));
            }
        } else {
            this.map.getViewport().style.cursor = '';
            
            // Reset hover style
            if (hoveredFeature) {
            hoveredFeature.setStyle(undefined);
            hoveredFeature = null;
            }
        }
        });
        } catch (err) {
          console.error('Map loading error:', err);
          this.$refs.mapContainer.innerHTML = `
            <div class="map-error">
              <p>Failed to load map for ${this.state}</p>
              <small>${err.message}</small>
            </div>
          `;
        }
      }
    },
    beforeDestroy() {
      if (this.map) {
        this.map.setTarget(undefined);
        this.map.un('pointermove'); // clean listener
      }
      if (this.tooltipOverlay) {
        document.body.removeChild(this.tooltipOverlay.getElement());
      }
    }
  };
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">

    <div id="map" ref="mapContainer" class="w-full h-full tw-absolute tw-cursor-pointer">
        <!-- <div class="tw-absolute glass tw-bg-white tw-h-[200px] tw-right-4 tw-top-4 tw-w-[150px] tw-text-gray-700 tw-rounded-lg tw-p-2">
        </div> -->
    </div>
    <div id="mapInfo" class="tw-absolute tw-bg-white tw-text-gray-700 tw-rounded-lg tw-shadow-md tw-p-2"></div>

        </div>
    </AppLayout>
</template>

  <style scoped>
  #map {
    width: 100%;
    height: 100%;
  }
  
  .map-error {
    padding: 20px;
    color: #721c24;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
  }

  #mapInfo {
        position: absolute;
        display: inline-block;
        height: auto;
        width: auto;
        z-index: 100;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 4px;
        padding: 5px;
        left: 50%;
        transform: translateX(3%);
        visibility: hidden;
        pointer-events: none;
      }
.glass {
    background-color: #3f8a36;
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border-radius: 16px;
  transition: all 0.3s ease-in-out;

  transform-origin: top left;
  z-index: 10;
}



.glass::after {
  content: "" !important;
  display: block !important;
  position: absolute !important;
  width: 16px !important;
  height: 19px !important;
  background: white !important;
  z-index: 0 !important;
  left: 90% !important;
  filter: blur(16px) !important;
  opacity: 1 !important;
  border-color: transparent !important;
  top: 50% !important;
}

.glass::before {
  content: "";
  display: block;
  position: absolute;
  width: 16px;
  height: 19px;
  background: linear-gradient(135deg, #ebe046 -20%, #ffac01 120%);
  z-index: 0;
  left: 10%;
  filter: blur(15px);
  top: 40%;
}

  </style>