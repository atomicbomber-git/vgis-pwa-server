import Swal from "sweetalert2";
import { get } from "lodash"

export default {
    confirmationModal: function (args) {
        return Swal.fire({
            titleText: "Konfirmasi",
            text: "Apakah Anda yakin Anda ingin melakukan tindakan ini?",
            icon: "question",
            showCancelButton: true,
            ...args,
        })
    },

    errorModal: function (args) {
        return Swal.fire({
            titleText: get(args, "titleText", "Error"),
            text: get(args, "text", "Terjadi kesalahan pada sistem."),
            icon: get(args, "icon", "error"),
        })
    },

    successModal: function (
        titleText = "Success",
        text = "Tindakan berhasil.",
        icon = "success",
    ) {
        return Swal.fire({
            titleText: titleText,
            text: text,
            icon: icon,
        })
    },

    loadingModal: function () {
        return Swal.fire({
            onBeforeOpen() {
                Swal.showLoading()
            }
        })
    }
}
