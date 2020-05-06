<template>
    <div class="row no-gutters">
        <div class="col-md">
            <div class="card">
                <div class="card-body p-0">
                    <gmap-map
                        @click="onMapClick"
                        :center="{
                            lat: map_config.center.latitude,
                            lng: map_config.center.longitude
                        }"
                        :zoom="map_config.zoom"
                        :style="{
                            height: '600px'
                        }"
                        map-type-id="terrain"
                    >
                        <!-- Marker pointer -->
                        <gmap-marker
                            :position="{
                                lat: pointer_marker.latitude,
                                lng: pointer_marker.longitude
                            }"
                        />

                        <!-- Marker penanda lokasi panorama -->
                        <gmap-marker
                            v-for="panorama in panoramas"
                            :key="panorama.id"
                            @click="onPanoramaMarkerClick(panorama)"
                            :position="{
                                lat: panorama.latitude,
                                lng: panorama.longitude
                            }"
                        />
                    </gmap-map>
                </div>
            </div>
        </div>

        <div class="col-md border p-3">

            <fieldset>
                <legend> Data </legend>

                <form @submit.prevent="onFormSubmit">
                    <div class="form-group row">
                        <div class="col">
                            <label for="latitude">
                                Latitude:
                            </label>

                            <input
                                v-model.number="pointer_marker.latitude"
                                class="form-control"
                                placeholder="Latitude"
                                :class="{
                                    'is-invalid': get(error_data, 'errors.latitude[0]', false)
                                }"
                                id="latitude"
                                type="number"
                                step="any"
                            >
                            <span class="invalid-feedback">
                                {{ get(error_data, 'errors.latitude[0]', '') }}
                            </span>
                        </div>

                        <div class="col">
                            <div class="form-group">
                                <label for="longitude">
                                    Longitude:
                                </label>

                                <input
                                    v-model.number="pointer_marker.longitude"
                                    class="form-control"
                                    placeholder="Longitude"
                                    :class="{
                                        'is-invalid': get(error_data, 'errors.longitude[0]', false)
                                    }"
                                    id="longitude"
                                    type="text"
                                >
                                <span class="invalid-feedback">
                                    {{ get(error_data, 'errors.longitude[0]', '') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name">
                            Name:
                        </label>

                        <input
                            v-model.number="name"
                            class="form-control"
                            placeholder="Name"
                            :class="{
                                'is-invalid': get(error_data, 'errors.name[0]', false)
                            }"
                            id="name"
                            type="text"
                        >
                        <span class="invalid-feedback">
                            {{ get(error_data, 'errors.name[0]', '') }}
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="description">
                            Description:
                        </label>

                        <textarea
                            v-model.number="description"
                            class="form-control"
                            placeholder="Description"
                            :class="{
                                'is-invalid': get(error_data, 'errors.description[0]', false)
                            }"
                            id="description"
                            type="text"
                        ></textarea>
                        <span class="invalid-feedback">
                            {{ get(error_data, 'errors.description[0]', '') }}
                        </span>
                    </div>

                    <div class="form-group">
                        <div class="custom-file">
                            <input
                                @change="onGambarInputChange"
                                ref="image_input"
                                type="file"
                                class="custom-file-input"
                                id="image">
                            <label class="custom-file-label"
                                   for="image">
                                {{ get(this.image_file, "name", "Pilih Berkas Gambar") }}
                            </label>
                        </div>

                        <img
                            v-if="image_file_url"
                            :src="image_file_url"
                            class="img-fluid rounded-top mt-3"
                            alt="">
                    </div>

                    <div class="form-group d-flex justify-content-end">
                        <input
                            value="Create"
                            class="btn btn-primary"
                            type="submit">
                    </div>
                </form>
            </fieldset>
        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import modal from "../modal";
    import {get} from "lodash"
    import Swal from "sweetalert2"

    export default {
        props: {
            map_config: Object,
            panoramas: Array,
            submit_url: String,
            redirect_url: String,
        },

        data() {
            return {
                selected_panorama: null,
                pointer_marker: {
                    latitude: this.map_config.latitude,
                    longitude: this.map_config.longitude,
                },

                name: null,
                description: null,
                image_file: null,
                image_file_url: null,
            }
        },

        watch: {
            selected_panorama(new_selected_panorama) {
                if (new_selected_panorama === null) {
                    return
                }
            },

            image_file(new_image_file) {
                if (new_image_file === null) { return }

                let reader = new FileReader()
                reader.addEventListener('load', () => {
                    this.image_file_url = reader.result
                }, false)

                reader.readAsDataURL(new_image_file)
            }
        },

        computed: {
            form_data() {
                return {
                    name: this.name,
                    description: this.description,
                    latitude: this.pointer_marker.latitude,
                    longitude: this.pointer_marker.longitude,
                    image: this.image_file,
                }
            },

            processed_form_data() {
                let formData = new FormData()

                Object.keys(this.form_data)
                    .forEach(key => {
                        if (this.form_data[key]) {
                            formData.append(key, this.form_data[key])
                        }
                    })

                return formData
            }
        },

        methods: {
            onMapClick(e) {
                this.pointer_marker.latitude = e.latLng.lat()
                this.pointer_marker.longitude = e.latLng.lng()
            },

            onGambarInputChange(e) {
                this.image_file = e.target.files[0]
            },

            onFormSubmit() {
                modal.confirmationModal()
                    .then(result => {
                        if (!result.value) throw new Error()

                        modal.loadingModal()

                        return axios.post(this.submit_url, this.processed_form_data, {
                            headers: {'Content-Type': 'multipart/form-data' }
                        })
                    })
                    .then(() => {
                        Swal.close()
                        return modal.successModal()
                    })
                    .then(() => {
                        window.location.replace(this.redirect_url)
                    })
                    .catch(error => {
                        Swal.close()
                        let error_data = get(error, "response.data", null);
                        if (error_data) this.error_data = error_data;
                    })
            }
        }
    }
</script>
