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

                            <!-- Poligon konektor baru -->
<!--                            <GmapPolyline-->
<!--                                v-for="link in panorama.panorama_links"-->
<!--                                :key="link.id"-->
<!--                                :path="[{lat: panorama.latitude, lng: panorama.longitude}, {lat: link.end.latitude, lng: link.end.longitude}]"-->
<!--                            />-->
                        </template>
                    </gmap-map>

                    <div class="card"
                         v-if="selected_panorama">
                        <div class="card-body">

                            <div
                                v-if="in_connecting_mode"
                                class="alert alert-warning">
                                <i class="fas fa-info-circle  "></i>
                                Anda berada pada mode penghubungan panorama. Silahkan klik penanda panorama
                                lain untuk membuat hubungan baru.
                            </div>

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
                                        Tutup VT
                                        <i class="fas fa-arrow-circle-left  "></i>
                                    </button>
                                </div>

                                <div class="d-flex justify-content-end">

                                    <button
                                        @click="onConnectButtonClick"
                                        class="btn btn-sm mr-2"
                                        :class="{
                                            'btn-primary': !this.in_connecting_mode,
                                            'btn-warning': this.in_connecting_mode,
                                        }"
                                    >
                                        {{ !this.in_connecting_mode ? 'Hubungkan' : 'Batal Hubungkan' }}
                                        <i class="fas fa-link"></i>
                                    </button>


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
                m_panoramas: [...this.panoramas],
                in_connecting_mode: false,
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
            onConnectButtonClick() {
                this.in_connecting_mode = !this.in_connecting_mode
            },

            onPanoramaMarkerClick(panorama) {
                if (!this.in_connecting_mode) {
                    this.selected_panorama = panorama
                    return
                }

                /* Need to check if there's a link already here */
                let existing_link_count = this.selected_panorama.panorama_links
                    .filter(link => link.panorama_end_id === panorama.id)
                    .length

                if (existing_link_count > 0) {
                    alert("Hubungan telah ada.")
                    return
                }

                modal.confirmationModal()
                    .then(response => {
                        if (!response.value) { throw new Error() }
                        return axios.post(`/panorama-link`, {
                            panorama_start_id: this.selected_panorama.id,
                            panorama_end_id: panorama.id,
                        })
                    })
                    .then(response => {
                        this.selected_panorama.panorama_links.push(response.data.start_link)

                        panorama.panorama_links.push(response.data.end_link)
                        this.gmap_panorama.setPano(`${panorama.id}`)

                        return modal.successModal()
                    })
                    .then(() => {
                        this.in_connecting_mode = false
                    })
                    .catch(error => {
                        this.in_connecting_mode = false
                        modal.errorModal()
                    })
            },

            onMapClick(e) {
                this.pointer_marker.latitude = e.latLng.lat()
                this.pointer_marker.longitude = e.latLng.lng()
            },

            resetState() {
                this.in_connecting_mode = false
                this.selected_panorama = null
            },

            onCloseVirtualTourButtonClick() {
                this.resetState()
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
                        this.m_panoramas = this.m_panoramas
                            .filter(panorama => panorama.id !== this.selected_panorama.id)
                            .map(panorama => {
                                return {
                                    ...panorama,
                                    panorama_links: panorama.panorama_links.filter(link => {
                                        return (link.panorama_end_id !== this.selected_panorama.id) &&
                                            (link.panorama_start_id !== this.selected_panorama.id)
                                    })
                                }
                            })

                        this.resetState()
                    })
            },

            initPanorama(panorama) {
                if (!this.gmap_panorama) {
                    this.gmap_panorama = new google.maps.StreetViewPanorama(
                        this.$refs.streetview_ref,
                        {pano: `${panorama.id}`}
                    );

                    /* Register panorama provider */
                    this.gmap_panorama.registerPanoProvider(search_pano_id => {
                        let panorama = this.m_panoramas.find(panorama => panorama.id == search_pano_id)
                        if (panorama) {
                            return this.getPanoramaData(panorama)
                        }

                        return null
                    });

                    this.map.setStreetView(this.gmap_panorama)
                    return
                }

                this.gmap_panorama.setPano(`${panorama.id}`)
            },

            getPanoramaData(panorama) {
                return {
                    location: {
                        pano: `${panorama.id}`,  // The ID for this custom panorama.
                        description: panorama.nama,
                        latLng: new google.maps.LatLng(panorama.latitude, panorama.longitude)
                    },

                    links: panorama.panorama_links.map(link => {
                        return {
                            heading: link.heading,
                            description: link.end.deskripsi,
                            pano: `${link.panorama_end_id}`,
                        }
                    }),

                    copyright: 'Imagery (c) 2010 Rizki Oktaviano',
                    tiles: {
                        tileSize: new google.maps.Size(1024, 512),
                        worldSize: new google.maps.Size(1024, 512),
                        centerHeading: 0,
                        getTileUrl: (pano, zoom, tileX, tileY) => {
                            return `/panorama-image/${panorama.id}/${zoom}/${tileX}/${tileY}`;
                        }
                    }
                };
            },
        },
    }
</script>
