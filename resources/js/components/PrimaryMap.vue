<template>
    <div class="row no-gutters">
        <div class="col-md">
            <div class="card">
                <div class="card-body p-0">
                    <gmap-map
                        @click="onMapClick"
                        ref="map_ref"
                        :center="{lat: map_config.center.latitude, lng: map_config.center.longitude}"
                        :zoom="map_config.zoom"
                        :style="{
                            height: '600px'
                        }"
                        map-type-id="terrain"
                    >
                        <!-- Marker pointer -->
                        <gmap-marker
                            v-if="has_pointer_marker"
                            :position="{lat: pointer_marker.latitude, lng: pointer_marker.longitude}"
                        />

                        <!-- Marker penanda lokasi panorama -->
                        <gmap-marker
                            v-for="panorama in panoramas"
                            :key="panorama.id"
                            @click="onPanoramaMarkerClick(panorama)"
                            :position="{lat: panorama.latitude, lng: panorama.longitude}"
                        />
                    </gmap-map>
                </div>
            </div>
        </div>

        <div v-show="selected_panorama" class="col">
            <div ref="streetview_ref" style="height: 100%">
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            map_config: Object,
            panoramas: Array,
            has_street_view: Boolean,
            has_pointer_marker: Boolean,
        },

        data() {
            return {
                selected_panorama: null,
                pointer_marker: {
                    latitude: this.map_config.latitude,
                    longitude: this.map_config.longitude,
                },
            }
        },

        mounted() {
            this.$refs.map_ref.$mapPromise.then(map => {
                this.map = map;
            })
        },

        watch: {
            selected_panorama(new_selected_panorama) {
                if (new_selected_panorama === null) { return }
                this.initPanorama(new_selected_panorama)
            }
        },

        methods: {
            onMapClick(e) {
                this.pointer_marker.latitude = e.latLng.lat()
                this.pointer_marker.longitude = e.latLng.lng()
            },

            onPanoramaMarkerClick(panorama) {
                if (this.has_street_view) {
                    this.selected_panorama = panorama
                }
            },

            initPanorama(panorama) {
                let gmap_panorama = new google.maps.StreetViewPanorama(
                    this.$refs.streetview_ref,
                    { pano: `${panorama.id}` }
                );

                /* Register panorama provider */
                gmap_panorama.registerPanoProvider(search_pano_id => {
                    if (this.panoramas.find(panorama => panorama.id == search_pano_id)) {
                        return this.getPanoramaData(panorama);
                    }

                    return null;
                });

                this.map.setStreetView(gmap_panorama);
            },

            getPanoramaData(panorama) {
                return {
                    location: {
                        pano: panorama.id,  // The ID for this custom panorama.
                        description: panorama.nama,
                        latLng: new google.maps.LatLng(panorama.latitude, panorama.longitude)
                    },

                    copyright: 'Imagery (c) 2010 Rizki Oktaviano',
                    tiles: {
                        tileSize: new google.maps.Size(128, 64),
                        worldSize: new google.maps.Size(1024, 512),
                        centerHeading: 105,
                        getTileUrl: (pano, zoom, tileX, tileY) => {
                            return `/panorama-image/${panorama.id}/${zoom}/${tileX}/${tileY}`;
                        }
                    }
                };
            },
        }
    }
</script>
