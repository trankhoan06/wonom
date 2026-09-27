<?php
// Khởi tạo model WPOption và gắn vào form để lấy dữ liệu từ bảng options
$model = new \TypeRocket\Models\WPOption();
$form = tr_form('options', 'create', null, $model)->useJson()->setGroup('tr_theme_options');

echo '<p>Quản lý logo đối tác và khách hàng.</p>';

// Fix lỗi ảnh SVG bị ẩn (width 1px, height 1px) khi load lại trang
echo '<style>
    .typerocket-container img.attachment-thumbnail {
        width: auto !important;
        height: auto !important;
        max-width: 150px;
        max-height: 150px;
        min-width: 50px;
        min-height: 50px;
    }
</style>';

echo $form->open();

echo $form->text('home_clients_tab1_name')->setLabel("Tên Tab 1 (VD: Real Estate Developers)");
echo $form->repeater('home_clients_tab1')->setLabel("Logo Tab 1")->setFields([
    $form->image('logo')->setLabel("Logo đối tác"),
    $form->text('link')->setLabel("Link (Optional)")
]);

echo $form->text('home_clients_tab2_name')->setLabel("Tên Tab 2 (VD: Real Estate Projects)");
echo $form->repeater('home_clients_tab2')->setLabel("Logo Tab 2")->setFields([
    $form->image('logo')->setLabel("Logo đối tác"),
    $form->text('link')->setLabel("Link (Optional)")
]);

echo $form->text('home_clients_tab3_name')->setLabel("Tên Tab 3 (VD: Others Industry)");
echo $form->repeater('home_clients_tab3')->setLabel("Logo Tab 3")->setFields([
    $form->image('logo')->setLabel("Logo đối tác"),
    $form->text('link')->setLabel("Link (Optional)")
]);

echo $form->row(
    $form->text('home_clients_btn_text')->setLabel("Text nút bấm"),
    $form->text('home_clients_btn_link')->setLabel("Link nút bấm")
);

echo $form->submit('Save Options');
echo $form->close();
