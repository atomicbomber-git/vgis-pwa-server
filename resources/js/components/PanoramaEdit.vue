<template>
  <div class="row no-gutters">
    <div class="col-md">
      <div class="card h-100">
        <div class="card-body p-0 d-flex flex-column">
          <gmap-map
              class="flex-fill"
              @click="onMapClick"
              :center="{
                            lat: this.panorama.latitude,
                            lng: this.panorama.longitude
                        }"
              :zoom="map_config.zoom"
              :style="{
                            'min-height': '500px'
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

    <div class="col-md border p-3"
         style="min-height: 600px"
    >
      <fieldset>
        <legend> Data</legend>

        <form @submit.prevent="onFormSubmit">
          <div class="form-group row">
            <div class="col-md-6 col-sm-12">
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

            <div class="col-md-6 col-sm-12 mt-2 mt-sm-0">
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
            <div class="custom-control custom-checkbox">
              <input type="checkbox"
                     v-model="is_first"
                     class="custom-control-input"
                     id="is_first"
              >
              <label class="custom-control-label"
                     for="is_first"
              > First Panorama
              </label>
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
            <div class="mb-2"> Current Image:</div>

            <img :src="`/panorama-original-image/${panorama.id}`"
                 class="img-fluid rounded-top"
                 alt=""
            >
          </div>

          <div class="form-group">
            <div class="mb-2"> New Image:</div>

            <div class="custom-file">
              <input
                  @change="onGambarInputChange"
                  ref="image_input"
                  type="file"
                  class="custom-file-input"
                  id="image"
              >
              <label class="custom-file-label"
                     for="image"
              >
                {{ get(this.image_file, "name", "Pick Image") }}
              </label>
            </div>

            <img
                v-if="image_file_url"
                :src="image_file_url"
                class="img-fluid rounded-top mt-3"
                alt=""
            >
          </div>

          <div class="form-group d-flex justify-content-end">
            <input
                value="Update"
                class="btn btn-primary"
                type="submit"
            >
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
    panorama: Object
  },

  data() {
    return {
      selected_panorama: null,
      pointer_marker: {
        latitude: this.panorama.latitude,
        longitude: this.panorama.longitude,
      },

      name: this.panorama.name,
      description: this.panorama.description,
      image_file: null,
      image_file_url: null,
      is_first: this.panorama.is_first,
    }
  },

  watch: {
    image_file(new_image_file) {
      if (new_image_file === null) {
        return
      }

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
        is_first: this.is_first ? 1 : 0,
        _method: 'put',
      }
    },

    processed_form_data() {
      let formData = new FormData()

      Object.keys(this.form_data)
          .forEach(key => {
            if (this.form_data[key] !== null) {
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
              headers: {'Content-Type': 'multipart/form-data'}
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
            if (!error.isAxiosError) {
              return
            }

            Swal.close()
            let error_data = get(error, "response.data", null);
            if (error_data) this.error_data = error_data;
          })
    }
  }
}
</script>
