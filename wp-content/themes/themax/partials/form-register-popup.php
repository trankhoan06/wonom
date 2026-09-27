
<form id="form_register_popup" class="form  register font-1">
    
   
        <div class="form-group">
            <input class="form-control" name="fullname" type="text" placeholder="<?= __("Họ tên","tbs") ?> (*)" required>
        </div>
        <div class="form-group">
            <input class="form-control" name="mobile" type="text" placeholder="<?= __("Điện thoại","tbs") ?> (*)" required  pattern="^[\d\+\s\(\)]{10,10}$" data-fv-regexp-message="<?= __("Số điện thoại không hợp lệ","tbs") ?>" data-fv-notempty-message="<?= __("Vui lòng nhập nội dung","tbs") ?>">
        </div>
        <div class="form-group">
            <input class="form-control" name="email" type="email" placeholder="<?= __("Email","tbs") ?>">
        </div>
         <div class="form-group">
            <select class="form-select" name="interest">
                <option value=""><?= __("Chọn loại căn hộ bạn quan tâm","tbs") ?></option>
                <option value="<?= __("1 Phòng ngủ","tbs") ?>"><?= __("1 Phòng ngủ","tbs") ?></option>
                <option value="<?= __("2 Phòng ngủ","tbs") ?>"><?= __("2 Phòng ngủ","tbs") ?></option>
                <option value="<?= __("3 Phòng ngủ","tbs") ?>"><?= __("3 Phòng ngủ","tbs") ?></option>
            </select>
        </div>

        <div class="form-group">
            <textarea class="form-control" name="content" placeholder="<?= __("Nội dung","tbs") ?>"></textarea>
        </div>

        <div class="mt-4 text-center">
            <button class="btn btn-primary relative submit " type="submit">
               <span><?= __("Đăng ký ngay","tbs") ?></span> <i class="fal fa-long-arrow-right"></i>
            </button>
        </div>
   
    
</form>
