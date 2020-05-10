import Swal from "sweetalert2";
import { get } from "lodash"

export default {
    confirmationModal: function (args) {
        return Swal.fire({
            titleText: "Confirmation",
            text: "Are you sure you want to perform this action?",
            icon: "question",
            showCancelButton: true,
            ...args,
        })
    },

    errorModal: function (args) {
        return Swal.fire({
            titleText: get(args, "titleText", "Error"),
            text: get(args, "text", "There was a system error."),
            icon: get(args, "icon", "error"),
        })
    },

    successModal: function (
        titleText = "Success",
        text = "Action successful.",
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
