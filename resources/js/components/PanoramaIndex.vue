<template>
    <div class="row no-gutters">
        <div class="col-md">
            <div class="card">
                <div class="card-body p-0 d-flex flex-column"
                     style="height: 600px">
                    <div class="flex-grow-1">
                        <gmap-map
                            ref="map_ref"
                            :center="{lat: map_config.center.latitude, lng: map_config.center.longitude}"
                            :zoom="map_config.zoom"
                            :style="{ height: '100%' }"
                            map-type-id="terrain"
                        >
                            <gmap-polyline
                                v-if="in_connecting_mode && current_mouse_position"
                                :options="{
                                    strokeColor: '#ffc800',
                                    strokeOpacity: 0.5,
                                    strokeWeight: 2
                                }"
                                :path="[
                                    {lat: selected_panorama.latitude, lng: selected_panorama.longitude},
                                    {lat: current_mouse_position.latitude, lng: current_mouse_position.longitude}
                                ]"
                            />

                            <template v-for="panorama in m_panoramas">
                                <!-- Marker penanda lokasi panorama -->
                                <gmap-marker
                                    :key="panorama.id + '_marker'"
                                    @click="onPanoramaMarkerClick(panorama)"
                                    :position="{lat: panorama.latitude, lng: panorama.longitude}"
                                />

                                <!-- Poligon panorama -->
                                <gmap-polyline
                                    :options="{
                                    strokeColor: '#9a3737',
                                    strokeOpacity: 0.5,
                                    strokeWeight: 2
                                }"
                                    v-for="link in panorama.panorama_links"
                                    @click="onPanoramaLinkLineClick($event, link)"
                                    :key="link.id"
                                    :path="[{lat: panorama.latitude, lng: panorama.longitude}, {lat: link.end.latitude, lng: link.end.longitude}]"
                                />
                            </template>
                        </gmap-map>
                    </div>


                    <div class="card"
                         v-if="selected_panorama">
                        <div class="card-body">
                            <h2 class="h4">
                                <i class="fas fa-map-marker"></i>
                                {{ selected_panorama.name }}
                            </h2>

                            <span
                                v-if="selected_panorama.is_first"
                                class="badge badge-pill badge-primary">
                                First Panorama
                            </span>

                            <p>
                                {{ selected_panorama.description }}
                            </p>

                            <div style="max-height: 100px; overflow-y: scroll" class="my-2">
                                <div
                                    v-if="this.selected_panorama.panorama_links.length === 0"
                                    class="alert alert-info">
                                    This panorama doesn't have any link.
                                </div>


                                <table
                                    v-if="this.selected_panorama.panorama_links.length > 0"
                                    class="table table-sm table-bordered table-striped">
                                    <thead class="thead thead-dark">
                                    <tr>
                                        <th> # </th>
                                        <th> Target </th>
                                        <th class="text-center">
                                            <i class="fas fa-wrench"></i>
                                        </th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    <tr v-for="(link, index) in selected_panorama.panorama_links">
                                        <td> {{ index + 1 }}</td>
                                        <td> {{ link.end.name }}</td>
                                        <td class="text-center">
                                            <button @click="onLinkDeleteButtonClick(link)" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between">
                                <div class="d-flex justify-content-start">
                                    <button
                                        @click="onCloseVirtualTourButtonClick"
                                        class="btn btn-secondary btn-sm">
                                        Close Tour
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
                                        {{ !this.in_connecting_mode ? 'Connect' : 'Connect (Cancel)' }}
                                        <i class="fas fa-link"></i>
                                    </button>

                                    <a class="btn btn-primary btn-sm mr-2"
                                        :href="`/panorama/${selected_panorama.id}/edit`"
                                    >
                                        Edit
                                        <i class="fas fa-pencil-alt  "></i>
                                    </a>


                                    <form @submit.prevent="onPanoramaDeleteButtonClick">
                                        <button class="btn btn-danger btn-sm">
                                            Delete
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
    import {throttle} from "lodash"

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
                selected_panorama_link_position: null,
                selected_panorama_link: null,
                current_mouse_position: null,
            }
        },

        mounted() {
            this.$refs.map_ref.$mapPromise.then(map => {
                this.map = map;

                map.addListener('mousemove', throttle(e => {
                    this.current_mouse_position = {
                        latitude: e.latLng.lat(),
                        longitude: e.latLng.lng(),
                    }
                }, 200))
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
            panoramaLinksToSVLinks(panorama_links) {
                return panorama_links.map(link => {
                    return {
                        heading: link.heading,
                        description: link.end.description,
                        pano: `${link.panorama_end_id}`,
                    }
                })
            },

            onLinkDeleteButtonClick(link) {
                modal.confirmationModal()
                    .then(response => {
                        if (!response.value) { throw new Error() }
                        return axios.delete(`/panorama-link/${link.id}`);
                    })
                    .then(response => {
                        /* Revise links */
                        this.m_panoramas = this.m_panoramas.map(panorama => {
                            return {
                                ...panorama,
                                panorama_links: panorama.panorama_links.filter(p_link => {
                                    if (
                                        (p_link.panorama_start_id === link.panorama_start_id) &&
                                        (p_link.panorama_end_id === link.panorama_end_id)
                                    ) {
                                        return false
                                    }

                                    if (
                                        (p_link.panorama_start_id === link.panorama_end_id) &&
                                        (p_link.panorama_end_id === link.panorama_start_id)
                                    ) { return false }

                                    return true
                                })
                            }
                        })

                        this.gmap_panorama.setLinks(
                            this.panoramaLinksToSVLinks(this.selected_panorama.panorama_links)
                        )

                        this.selected_panorama = this.m_panoramas.find(pano => pano.id === this.selected_panorama.id)
                        modal.successModal()
                    })
                    .catch(error => {
                        if (!error.isAxiosError) { return }
                        modal.errorModal()
                    })
            },

            onPanoramaLinkLineClick(e, link) {
                this.selected_panorama_link_position = {
                    latitude: e.latLng.lat(),
                    longitude: e.latLng.lng(),
                }

                this.selected_panorama_link = link
            },

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
                    modal.errorModal({
                        text: "Connection already exists."
                    })
                    return
                }

                modal.confirmationModal()
                    .then(response => {
                        if (!response.value) {
                            throw new Error()
                        }
                        return axios.post(`/panorama-link`, {
                            panorama_start_id: this.selected_panorama.id,
                            panorama_end_id: panorama.id,
                        })
                    })
                    .then(response => {
                        // Update existing links with the new ones
                        this.selected_panorama.panorama_links.push(response.data.start_link)
                        panorama.panorama_links.push(response.data.end_link)

                        // Refresh panorama
                        this.gmap_panorama.setLinks(
                            this.panoramaLinksToSVLinks(this.selected_panorama.panorama_links)
                        )

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
                        description: panorama.name,
                        latLng: new google.maps.LatLng(panorama.latitude, panorama.longitude)
                    },

                    links: this.panoramaLinksToSVLinks(panorama.panorama_links),

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
