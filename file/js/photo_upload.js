/* ==========================================================================
 * ລະບົບຖ່າຍຮູບ / ເລືອກຮູບ — ຮອງຮັບທັງ Android ແລະ iOS
 * ໃຊ້ຮ່ວມກັນລະຫວ່າງ vacancy_add.php ແລະ vacancy_edit.php
 * ==========================================================================
 *
 * ໂຄງສ້າງ HTML ທີ່ຕ້ອງການ (<target> = ຊື່ຫຍໍ້ຂອງກ່ອງ ເຊັ່ນ photo):
 *
 *   <div class="upload-box" id="box-<target>" data-photo-box="<target>" data-custom-preview>
 *       <button class="btn-remove-img" id="btn-remove-<target>"          (ມີ ຫຼື ບໍ່ມີກໍ່ໄດ້)
 *               data-target="<target>" data-flag="remove_xxx">...</button>
 *       <div class="upload-content" id="content-<target>">
 *           <button class="btn-pick" data-target="<target>" data-mode="camera">...</button>
 *           <button class="btn-pick" data-target="<target>" data-mode="gallery">...</button>
 *       </div>
 *       <div class="preview-container d-none" id="preview-box-<target>">
 *           <img id="img-preview-<target>">
 *       </div>
 *       <input type="file" name="xxx" id="file-<target>" accept="image/*" class="file-hidden">
 *       <input type="file" name="xxx" id="cam-<target>"  accept="image/*"
 *              capture="environment" class="file-hidden" disabled>
 *       <input type="hidden" name="remove_xxx" id="remove_xxx" value="0">   (ສະເພາະໜ້າແກ້ໄຂ)
 *   </div>
 *
 * ໝາຍເຫດສຳຄັນ:
 *   - input 2 ຊ່ອງໃຊ້ name ດຽວກັນ ຊ່ອງທີ່ບໍ່ໄດ້ໃຊ້ຈະຖືກ disabled
 *     ເພື່ອບໍ່ໃຫ້ FormData ສົ່ງຄ່າຊ້ຳກັນ
 *   - accept ຕ້ອງເປັນ "image/*" ລ້ວນ ຫ້າມໃສ່ .heic
 *     ເພາະ iOS ຈະແປງ HEIC ເປັນ JPEG ໃຫ້ອັດຕະໂນມັດ ແຕ່ຖ້າລະບຸ .heic
 *     ມັນຈະສົ່ງໄຟລ໌ດິບມາ ເຊິ່ງ browser ແລະ server ສະແດງບໍ່ໄດ້
 *   - ຮູບທຸກໃບຈະຖືກບີບອັດຝັ່ງ browser ກ່ອນສົ່ງ ໃຫ້ບໍ່ເກີນ TARGET_BYTES (100KB)
 *     ໂດຍວົນລອງຫຼຸດຄຸນະພາບ → ຫຼຸດຂະໜາດ → ຫຼຸດຄຸນະພາບອີກ ຈົນກວ່າຈະໄດ້ (ເບິ່ງ compressImage())
 *     ຝັ່ງ server (insert_n_update_vacancy.php) ກໍ່ມີການບີບອັດຊ້ຳອີກຊັ້ນເປັນ fallback
 *     ສຳລັບກໍລະນີ browser ເກົ່າ ຫຼື ປິດ JavaScript
 * ========================================================================== */

(function ($) {
    'use strict';

    var MAX_SIDE = 1600;                  // ດ້ານທີ່ຍາວທີ່ສຸດຂອງຮູບ ກ່ອນເລີ່ມບີບອັດ (px)
    var TARGET_BYTES = 100 * 1024;        // ເປົ້າໝາຍ: ໄຟລ໌ທີ່ອັບໂຫຼດຂຶ້ນ server ຕ້ອງບໍ່ເກີນ 100KB
    // ຂັ້ນຕອນການລອງ: ຫຼຸດຄຸນະພາບກ່ອນ (ຮັກສາຄວາມຄົມ/ຕົວໜັງສືໄວ້ໃຫ້ດົນທີ່ສຸດ)
    // ຖ້າຫຼຸດຄຸນະພາບຈົນສຸດແລ້ວຍັງບໍ່ພໍ ຈຶ່ງຄ່ອຍຫຼຸດຂະໜາດ (scale) ລົງອີກຂັ້ນ ແລ້ວລອງຄຸນະພາບໃໝ່ທັງໝົດ
    var SCALE_STEPS = [1, 0.85, 0.7, 0.55, 0.42, 0.32];
    var QUALITY_STEPS = [0.85, 0.75, 0.65, 0.55, 0.45, 0.35];

    // ລວບລວມລາຍຊື່ກ່ອງອັບໂຫຼດທັງໝົດໃນໜ້ານີ້
    function allTargets() {
        return $('[data-photo-box]').map(function () {
            return $(this).data('photo-box');
        }).get();
    }

    // ກວດວ່າ browser ຂຽນໄຟລ໌ກັບເຂົ້າ input ໄດ້ບໍ່ (Chrome 62+ / Safari 14.1+)
    function canReplaceFile() {
        try {
            var dt = new DataTransfer();
            return typeof dt.items.add === 'function' && typeof File === 'function';
        } catch (e) {
            return false;
        }
    }

    function replaceFile(inputEl, file) {
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            inputEl.files = dt.files;
            return true;
        } catch (e) {
            return false;
        }
    }

    // ບີບອັດຮູບກ່ອນສົ່ງ — ວົນລອງຫຼຸດຄຸນະພາບ/ຂະໜາດ ຈົນກວ່າຈະໄດ້ບໍ່ເກີນ TARGET_BYTES (100KB)
    // ຫຼື ຈົນລອງຄົບທຸກຂັ້ນແລ້ວ (ກໍລະນີນີ້ຈະສົ່ງໄຟລ໌ທີ່ນ້ອຍທີ່ສຸດເທົ່າທີ່ລອງໄດ້ໄປແທນ)
    // callback ຈະໄດ້ຮັບ File ໃໝ່ ຫຼື null ຖ້າບໍ່ຕ້ອງ/ບໍ່ສາມາດບີບອັດໄດ້ (ໃຫ້ໃຊ້ໄຟລ໌ຕົ້ນສະບັບ)
    function compressImage(file, done) {
        if (!file || !file.type || file.type.indexOf('image/') !== 0) return done(null);
        if (file.type === 'image/gif') return done(null);   // ຮັກສາພາບເຄື່ອນໄຫວ
        if (file.size <= TARGET_BYTES) return done(null);   // ນ້ອຍພໍແລ້ວ ບໍ່ຕ້ອງບີບ
        if (!canReplaceFile() || !window.URL || !URL.createObjectURL) return done(null);

        var url = URL.createObjectURL(file);
        var img = new Image();

        img.onload = function () {
            try {
                var baseW = img.naturalWidth || img.width;
                var baseH = img.naturalHeight || img.height;
                var initialScale = Math.min(MAX_SIDE / Math.max(baseW, baseH), 1);
                baseW = Math.max(Math.round(baseW * initialScale), 1);
                baseH = Math.max(Math.round(baseH * initialScale), 1);

                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');

                if (!canvas.toBlob) {
                    URL.revokeObjectURL(url);
                    return done(null);
                }

                var best = null;   // blob ທີ່ນ້ອຍທີ່ສຸດເທົ່າທີ່ເຄີຍລອງໄດ້ (ສຳຮອງໄວ້ເຜື່ອບໍ່ຮອດເປົ້າໝາຍເລີຍ)
                var si = 0, qi = 0;

                function finish(blob) {
                    URL.revokeObjectURL(url);
                    // ຖ້າບີບແລ້ວບໍ່ນ້ອຍລົງ (ພົບໜ້ອຍຫຼາຍ ແຕ່ອາດເກີດກັບຮູບທີ່ຖືກບີບອັດແຮງມາກ່ອນແລ້ວ)
                    // ໃຫ້ໃຊ້ໄຟລ໌ຕົ້ນສະບັບແທນ ດີກວ່າສົ່ງໄຟລ໌ໃຫຍ່ກວ່າໂດຍບໍ່ຈຳເປັນ
                    if (!blob || blob.size >= file.size) return done(null);
                    var base = (file.name || 'photo').replace(/\.[^.]+$/, '');
                    try {
                        done(new File([blob], base + '.jpg', {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        }));
                    } catch (e) {
                        done(null);
                    }
                }

                function tryNext() {
                    if (si >= SCALE_STEPS.length) {
                        // ລອງຄົບທຸກຂັ້ນແລ້ວ ຍັງບໍ່ຮອດ 100KB → ໃຊ້ຕົວທີ່ນ້ອຍທີ່ສຸດທີ່ໄດ້
                        return finish(best);
                    }

                    var w = Math.max(Math.round(baseW * SCALE_STEPS[si]), 40);
                    var h = Math.max(Math.round(baseH * SCALE_STEPS[si]), 40);
                    canvas.width = w;
                    canvas.height = h;
                    // ແຕ້ມຈາກຮູບຕົ້ນສະບັບ (img) ທຸກຄັ້ງ ບໍ່ແມ່ນຈາກ canvas ຮອບກ່ອນ
                    // ເພື່ອບໍ່ໃຫ້ຄວາມເສຍຄຸນະພາບຈາກການບີບອັດຊ້ອນທັບກັນຫຼາຍຮອບ
                    ctx.clearRect(0, 0, w, h);
                    ctx.drawImage(img, 0, 0, w, h);

                    canvas.toBlob(function (blob) {
                        if (!blob) {
                            si++; qi = 0;
                            return tryNext();
                        }

                        if (!best || blob.size < best.size) best = blob;

                        if (blob.size <= TARGET_BYTES) {
                            return finish(blob);
                        }

                        qi++;
                        if (qi >= QUALITY_STEPS.length) {
                            qi = 0;
                            si++;
                        }
                        tryNext();
                    }, 'image/jpeg', QUALITY_STEPS[qi]);
                }

                tryNext();

            } catch (err) {
                URL.revokeObjectURL(url);
                done(null);
            }
        };

        img.onerror = function () {
            URL.revokeObjectURL(url);   // ເຊັ່ນ HEIC ທີ່ browser ອ່ານບໍ່ໄດ້ → ສົ່ງໄຟລ໌ດິບໄປ
            done(null);
        };

        img.src = url;
    }

    // ຈັດລະບຽບ input 2 ຊ່ອງ: ຊ່ອງໃດມີໄຟລ໌ໃຫ້ enable ຊ່ອງນັ້ນ ອີກຊ່ອງ disable
    function normalizeInputs(target) {
        var gal = document.getElementById('file-' + target);
        var cam = document.getElementById('cam-' + target);
        if (!gal || !cam) return;

        var camHas = cam.files && cam.files.length > 0;
        var galHas = gal.files && gal.files.length > 0;

        if (camHas && !galHas) {
            cam.disabled = false;
            gal.disabled = true;
        } else {
            // ມີແຕ່ຄັງຮູບ ຫຼື ບໍ່ມີໄຟລ໌ເລີຍ → ໃຫ້ຊ່ອງຄັງຮູບເປັນຕົວຫຼັກ
            gal.disabled = false;
            cam.disabled = true;
        }
    }

    function showPreview(target, file) {
        var $img = $('#img-preview-' + target);

        if (window.URL && URL.createObjectURL) {
            var old = $img.data('objurl');
            if (old) URL.revokeObjectURL(old);
            var url = URL.createObjectURL(file);
            $img.data('objurl', url).attr('src', url);
        }

        $('#content-' + target).addClass('d-none');
        $('#preview-box-' + target).removeClass('d-none');
        $('#box-' + target).addClass('has-file');
        $('#btn-remove-' + target).removeClass('d-none');
    }

    function clearPreview(target) {
        var $img = $('#img-preview-' + target);
        var old = $img.data('objurl');
        if (old && window.URL && URL.revokeObjectURL) URL.revokeObjectURL(old);

        $('#file-' + target).val('');
        $('#cam-' + target).val('');
        normalizeInputs(target);

        $img.removeData('objurl').removeAttr('src');
        $('#preview-box-' + target).addClass('d-none');
        $('#content-' + target).removeClass('d-none');
        $('#box-' + target).removeClass('has-file');
    }

    $(function () {

        var targets = allTargets();
        if (!targets.length) return;   // ໜ້ານີ້ບໍ່ມີກ່ອງອັບໂຫຼດ

        // ---------- ປຸ່ມ ຖ່າຍຮູບ / ເລືອກຈາກຄັງຮູບ ----------
        $(document).on('click', '.btn-pick', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var target = $(this).data('target');
            var mode = $(this).data('mode');          // camera | gallery
            var useEl = document.getElementById((mode === 'camera' ? 'cam-' : 'file-') + target);
            var otherEl = document.getElementById((mode === 'camera' ? 'file-' : 'cam-') + target);
            if (!useEl) return;

            // ຕ້ອງ enable ກ່ອນ ຈຶ່ງຈະ .click() ໄດ້
            useEl.disabled = false;
            if (otherEl) otherEl.disabled = true;

            // ຫ້າມໃສ່ setTimeout — .click() ຕ້ອງຢູ່ໃນ stack ດຽວກັບ user gesture
            // ບໍ່ດັ່ງນັ້ນ iOS Safari ຈະບລັອກການເປີດກ້ອງ
            useEl.click();
        });

        // ---------- ເມື່ອເລືອກ/ຖ່າຍຮູບແລ້ວ ----------
        var changeSelector = $.map(targets, function (t) {
            return '#file-' + t + ', #cam-' + t;
        }).join(', ');

        $(changeSelector).on('change', function () {
            var inputEl = this;
            var target = inputEl.id.replace(/^(file|cam)-/, '');

            normalizeInputs(target);

            if (!inputEl.files || inputEl.files.length === 0) return;

            var file = inputEl.files[0];

            // ຍົກເລີກຄຳສັ່ງລຶບຮູບ ເພາະຜູ້ໃຊ້ເລືອກຮູບໃໝ່ແລ້ວ (ສະເພາະໜ້າແກ້ໄຂ)
            var flagId = $('#btn-remove-' + target).data('flag');
            if (flagId) $('#' + flagId).val('0');

            $('#box-' + target).addClass('is-busy');

            compressImage(file, function (compressed) {
                if (compressed && replaceFile(inputEl, compressed)) {
                    file = compressed;
                    normalizeInputs(target);   // ຕັ້ງຄ່າ files ໃໝ່ ອາດ reset ສະຖານະ
                }
                $('#box-' + target).removeClass('is-busy');
                showPreview(target, file);
            });
        });

        // ຜູ້ໃຊ້ກົດຍົກເລີກໃນໜ້າຕ່າງເລືອກໄຟລ໌ → browser ບໍ່ຍິງ change
        // ຈຶ່ງຕ້ອງຈັດລະບຽບຄືນຕອນກັບມາທີ່ໜ້າຈໍ ບໍ່ດັ່ງນັ້ນຮູບເກົ່າຈະບໍ່ຖືກສົ່ງ
        $(window).on('focus', function () {
            $.each(targets, function (i, t) {
                normalizeInputs(t);
            });
        });

        // ---------- ປຸ່ມລົບຮູບ (ມີສະເພາະໜ້າແກ້ໄຂ) ----------
        $('.btn-remove-img').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();   // ບໍ່ໃຫ້ໄປເປີດໜ້າຕ່າງເລືອກໄຟລ໌

            var target = $(this).data('target');
            var flagId = $(this).data('flag');
            var $btn = $(this);

            function doRemove() {
                clearPreview(target);
                if (flagId) $('#' + flagId).val('1');
                $btn.addClass('d-none');
            }

            if (typeof Swal === 'undefined') {
                if (confirm('ຕ້ອງການລົບຮູບນີ້ບໍ່ ?')) doRemove();
                return;
            }

            Swal.fire({
                title: 'ຕ້ອງການລົບຮູບນີ້ບໍ່ ?',
                text: 'ຮູບຈະຖືກລຶບຖາວອນຫຼັງຈາກກົດປຸ່ມ ບັນທຶກ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ລຶບ',
                cancelButtonText: 'ຍົກເລີກ'
            }).then(function (result) {
                if (result.isConfirmed) doRemove();
            });
        });

    });

})(jQuery);
