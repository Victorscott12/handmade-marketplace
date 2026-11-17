<?php

/**
 * Thông báo validate tiếng Việt cho các rule hay dùng.
 * Rule nào thiếu thì Laravel tự dùng bản tiếng Anh (fallback_locale = en).
 * Thêm tên trường tiếng Việt ở mảng 'attributes' cuối file.
 */
return [
    'accepted'         => ':Attribute phải được chấp nhận.',
    'after'            => ':Attribute phải là ngày sau :date.',
    'after_or_equal'   => ':Attribute phải là ngày từ :date trở đi.',
    'alpha_dash'       => ':Attribute chỉ được chứa chữ, số, gạch ngang và gạch dưới.',
    'array'            => ':Attribute phải là một mảng.',
    'before'           => ':Attribute phải là ngày trước :date.',
    'between'          => [
        'array'   => ':Attribute phải có từ :min đến :max phần tử.',
        'file'    => ':Attribute phải có dung lượng từ :min đến :max KB.',
        'numeric' => ':Attribute phải nằm trong khoảng :min đến :max.',
        'string'  => ':Attribute phải có từ :min đến :max ký tự.',
    ],
    'boolean'          => ':Attribute phải là đúng hoặc sai.',
    'confirmed'        => ':Attribute nhập lại không khớp.',
    'current_password' => 'Mật khẩu hiện tại không đúng.',
    'date'             => ':Attribute không phải ngày hợp lệ.',
    'decimal'          => ':Attribute phải có :decimal chữ số thập phân.',
    'different'        => ':Attribute và :other phải khác nhau.',
    'digits'           => ':Attribute phải gồm :digits chữ số.',
    'digits_between'   => ':Attribute phải có từ :min đến :max chữ số.',
    'email'            => ':Attribute phải là địa chỉ email hợp lệ.',
    'exists'           => ':Attribute đã chọn không hợp lệ.',
    'file'             => ':Attribute phải là một tệp.',
    'gt'               => [
        'numeric' => ':Attribute phải lớn hơn :value.',
        'string'  => ':Attribute phải dài hơn :value ký tự.',
    ],
    'gte'              => [
        'numeric' => ':Attribute phải lớn hơn hoặc bằng :value.',
    ],
    'image'            => ':Attribute phải là hình ảnh.',
    'in'               => ':Attribute đã chọn không hợp lệ.',
    'integer'          => ':Attribute phải là số nguyên.',
    'lowercase'        => ':Attribute phải viết thường.',
    'lt'               => [
        'numeric' => ':Attribute phải nhỏ hơn :value.',
    ],
    'lte'              => [
        'numeric' => ':Attribute phải nhỏ hơn hoặc bằng :value.',
    ],
    'max'              => [
        'array'   => ':Attribute không được quá :max phần tử.',
        'file'    => ':Attribute không được lớn hơn :max KB.',
        'numeric' => ':Attribute không được lớn hơn :max.',
        'string'  => ':Attribute không được quá :max ký tự.',
    ],
    'mimes'            => ':Attribute phải có định dạng: :values.',
    'mimetypes'        => ':Attribute phải có định dạng: :values.',
    'min'              => [
        'array'   => ':Attribute phải có ít nhất :min phần tử.',
        'file'    => ':Attribute phải có dung lượng ít nhất :min KB.',
        'numeric' => ':Attribute phải tối thiểu là :min.',
        'string'  => ':Attribute phải có ít nhất :min ký tự.',
    ],
    'not_in'           => ':Attribute đã chọn không hợp lệ.',
    'numeric'          => ':Attribute phải là số.',
    'password'         => [
        'letters'       => ':Attribute phải chứa ít nhất một chữ cái.',
        'mixed'         => ':Attribute phải có cả chữ hoa và chữ thường.',
        'numbers'       => ':Attribute phải chứa ít nhất một chữ số.',
        'symbols'       => ':Attribute phải chứa ít nhất một ký tự đặc biệt.',
        'uncompromised' => ':Attribute này đã từng bị lộ, vui lòng chọn mật khẩu khác.',
    ],
    'regex'            => ':Attribute không đúng định dạng.',
    'required'         => 'Vui lòng nhập :attribute.',
    'required_if'      => 'Vui lòng nhập :attribute khi :other là :value.',
    'required_with'    => 'Vui lòng nhập :attribute khi có :values.',
    'same'             => ':Attribute và :other phải giống nhau.',
    'size'             => [
        'numeric' => ':Attribute phải bằng :size.',
        'string'  => ':Attribute phải có đúng :size ký tự.',
    ],
    'string'           => ':Attribute phải là chuỗi ký tự.',
    'unique'           => ':Attribute đã được sử dụng.',
    'uploaded'         => 'Tải lên :attribute thất bại.',
    'url'              => ':Attribute không phải đường dẫn hợp lệ.',

    'custom' => [],

    /*
     * Tên hiển thị của các trường. Mọi người thêm tên trường của module mình vào đây.
     */
    'attributes' => [
        'name'                  => 'tên',
        'email'                 => 'email',
        'phone'                 => 'số điện thoại',
        'password'              => 'mật khẩu',
        'password_confirmation' => 'mật khẩu nhập lại',
        'current_password'      => 'mật khẩu hiện tại',
        'avatar'                => 'ảnh đại diện',
        'account_type'          => 'loại tài khoản',
        'description'           => 'mô tả',
        'story'                 => 'câu chuyện',
        'address'               => 'địa chỉ',
        'price'                 => 'giá',
        'stock'                 => 'số lượng tồn kho',
        'category_id'           => 'danh mục',
        'images'                => 'hình ảnh',
        'images.*'              => 'hình ảnh',
        'quantity'              => 'số lượng',
        'rating'                => 'số sao',
        'comment'               => 'nội dung đánh giá',
        'receiver_name'         => 'tên người nhận',
        'receiver_phone'        => 'số điện thoại người nhận',
        'province'              => 'tỉnh/thành phố',
        'district'              => 'quận/huyện',
        'ward'                  => 'phường/xã',
        'address_line'          => 'địa chỉ cụ thể',
        'payment_method'        => 'phương thức thanh toán',
    ],
];
