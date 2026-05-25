<!-- below the form -->
 <div class="modal fade" id="cropModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Fix Image Ratio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <img id="cropImage" style="max-width:100%; display:block;">
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="cropBtn">Crop & Use Image</button>
      </div>
    </div>
  </div>
</div>




<script>
  let cropper;
let selectedFileName = '';

const cropModalEl = document.getElementById('cropModal');
const cropModal = new bootstrap.Modal(cropModalEl);
const cropImage = document.getElementById('cropImage');
const cropBtn = document.getElementById('cropBtn');

accoladeImage.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    if (file.size > 1 * 1024 * 1024) {
        showMessage('Error: Image size must be less than 1 MB.', false);
        this.value = '';
        return;
    }

    selectedFileName = file.name;

    const img = new Image();
    const objectUrl = URL.createObjectURL(file);

    img.onload = function () {
        const ratio = img.width / img.height;

        if (ratio >= 0.95 && ratio <= 1.05) {
            showMessage('', false);
            URL.revokeObjectURL(objectUrl);
            return;
        }

        cropImage.src = objectUrl;
        cropModal.show();

        cropModalEl.addEventListener('shown.bs.modal', function () {
            if (cropper) cropper.destroy();

            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
                background: false
            });
        }, { once: true });
    };

    img.src = objectUrl;
});



cropBtn.addEventListener('click', function () {
    if (!cropper) return;

    cropper.getCroppedCanvas({
        width: 500,
        height: 500,
        imageSmoothingQuality: 'high'
    }).toBlob(function (blob) {
        const croppedFile = new File([blob], selectedFileName, {
            type: 'image/jpeg',
            lastModified: Date.now()
        });

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(croppedFile);
        accoladeImage.files = dataTransfer.files;

        cropper.destroy();
        cropper = null;
        cropModal.hide();

        showMessage('Image ratio fixed successfully.', true);
    }, 'image/jpeg', 0.9);
});
</script>