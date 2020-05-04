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
                            :position="{lat: pointer_marker.latitude, lng: pointer_marker.longitude}"
                        />

                        <template v-for="panorama in panoramas">
                            <!-- Marker penanda lokasi panorama -->
                            <gmap-marker
                                :key="panorama.id + '_marker'"
                                @click="onPanoramaMarkerClick(panorama)"
                                :position="{lat: panorama.latitude, lng: panorama.longitude}"
                            />

                            <!-- Poligon panorama -->
                            <GmapPolyline
                                v-for="link in panorama.panorama_links"
                                :key="link.id"
                                :path="[{lat: panorama.latitude, lng: panorama.longitude}, {lat: link.end.latitude, lng: link.end.longitude}]"
                            />
                        </template>
                    </gmap-map>

                    <div class="card"
                         v-if="selected_panorama">
                        <div class="card-body">
                            <h2 class="h4">
                                <i class="fas fa-map-marker"></i>
                                {{ selected_panorama.nama }}
                            </h2>
                            <p>
                                {{ selected_panorama.deskripsi }}
                            </p>

                            <div class="d-flex justify-content-between">
                                <div class="d-flex justify-content-start">
                                    <button
                                        @click="onCloseVirtualTourButtonClick"
                                        class="btn btn-secondary btn-sm">
                                        Tutup Virtual Tour
                                        <i class="fas fa-arrow-circle-left  "></i>
                                    </button>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <form @submit.prevent="onPanoramaDeleteButtonClick">
                                        <button class="btn btn-danger btn-sm">
                                            Hapus
                                            <i class="fas fa-trash-alt "></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="selected_panorama"
             class="col">
            <div ref="streetview_ref"
                 style="height: 100%">
            </div>
        </div>
    </div>
</template>

<script>
    import modal from "../modal";
    import Swal from "sweetalert2"

    export default {
        props: {
            map_config: Object,
            panoramas: Array,
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
                if (new_selected_panorama === null) {
                    return
                }
                this.initPanorama(new_selected_panorama)
            }
        },

        methods: {
            onMapClick(e) {
                this.pointer_marker.latitude = e.latLng.lat()
                this.pointer_marker.longitude = e.latLng.lng()
            },

            onCloseVirtualTourButtonClick() {
                this.selected_panorama = null
            },

            onPanoramaDeleteButtonClick() {
                modal.confirmationModal()
                    .then(response => {
                        if (!response.value) throw new Error();
                        Swal.showLoading()
                        return axios.delete(`/panorama/${this.selected_panorama.id}`)
                    })
                    .then(response => {
                        Swal.hideLoading()

                        if (!response.status === 200) {
                            modal.errorModal()
                            return
                        }

                        return modal.successModal()
                    })
                    .then(() => {
                        this.panoramas = this.panoramas.filter(panorama => panorama.id !== this.selected_panorama.id)
                        this.selected_panorama = null
                    })
            },

            onPanoramaMarkerClick(panorama) {
                this.selected_panorama = panorama
            },

            initPanorama(panorama) {
                let gmap_panorama = new google.maps.StreetViewPanorama(
                    this.$refs.streetview_ref,
                    {pano: `${panorama.id}`}
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

                    links: panorama.panorama_links.map(link => {
                        return {
                            heading: link.heading,
                            description: link.end.nama,
                            pano: link.end.id,
                        }
                    }),

                    copyright: 'Imagery (c) 2010 Rizki Oktaviano',
                    tiles: {
                        tileSize: new google.maps.Size(1024, 512),
                        worldSize: new google.maps.Size(1024, 512),
                        centerHeading: 105,
                        getTileUrl: (pano, zoom, tileX, tileY) => {
                            return `/panorama-image/${panorama.id}/${zoom}/${tileX}/${tileY}`;
                        }
                    }
                };
            },
        },

        computed: {
            panorama_links() {
                return this.panoramas.reduce((current, next) => {
                    return [
                        ...current,
                        ...next.panorama_links
                    ]
                }, [])
            }
        }
    }
</script>
