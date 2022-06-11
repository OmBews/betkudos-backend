<?php

return [
    "accepted" => "Các: thuộc tính phải được chấp nhận.",
    "active_url" => "Các: thuộc tính không phải là một URL hợp lệ.",
    "after" => "Các: thuộc tính phải là một ngày sau: ngày.",
    "after_or_equal" => "Các: thuộc tính phải là một ngày sau hoặc bằng: ngày.",
    "alpha" => "Các: thuộc tính chỉ có thể chứa các chữ cái.",
    "alpha_dash" => "Các: thuộc tính chỉ có thể chứa các chữ cái, số, dấu gạch ngang và dấu gạch dưới.",
    "alpha_num" => "Các: thuộc tính chỉ có thể chứa các chữ cái và số.",
    "array" => "Các: thuộc tính phải là một mảng.",
    "base64_image" => "Hình ảnh được cung cấp phải là một hình ảnh Base64 hợp lệ với: Kích thước",
    "before" => "Các: thuộc tính phải là một ngày trước: ngày.",
    "before_or_equal" => "Các: thuộc tính phải là một ngày trước hoặc bằng: ngày.",
    "between" => [
        "array" => "Các: Thuộc tính phải có giữa: Min và: Max các mục.",
        "file" => "Các: thuộc tính phải nằm giữa: Min và: Max Kilobyte.",
        "numeric" => "Các: thuộc tính phải nằm giữa: Min và: Max.",
        "string" => "Các: thuộc tính phải nằm giữa: min và: ký tự MAX."
    ],
    "boolean" => "Trường: thuộc tính phải đúng hoặc sai.",
    "confirmed" => "Xác nhận: thuộc tính không khớp.",
    "custom" => [
        "password" => [
            "lowercase" => "Mật khẩu nên bao gồm ít nhất một chữ thường",
            "matches_username" => "Mật khẩu không nên quá giống với tên người dùng của bạn",
            "number" => "Mật khẩu nên bao gồm ít nhất một số",
            "uppercase" => "Mật khẩu nên bao gồm ít nhất một chữ cái viết hoa"
        ]
    ],
    "date" => "Các: thuộc tính không phải là một ngày hợp lệ.",
    "date_equals" => "Các: thuộc tính phải là một ngày bằng: ngày.",
    "date_format" => "Các: thuộc tính không khớp với định dạng: định dạng.",
    "different" => "Các: thuộc tính và: Khác phải khác nhau.",
    "digits" => "Các: thuộc tính phải là: chữ số chữ số.",
    "digits_between" => "Các: thuộc tính phải nằm giữa: tối thiểu và: chữ số tối đa.",
    "dimensions" => "Các: thuộc tính có kích thước hình ảnh không hợp lệ.",
    "distinct" => "Trường: thuộc tính có một giá trị trùng lặp.",
    "email" => "Các: thuộc tính phải là một địa chỉ email hợp lệ.",
    "ends_with" => "Các: thuộc tính phải kết thúc bằng một trong các thao tác sau :: Giá trị",
    "exists" => "Các thuộc tính đã chọn: không hợp lệ.",
    "file" => "Các: thuộc tính phải là một tập tin.",
    "filled" => "Trường: thuộc tính phải có giá trị.",
    "gt" => [
        "array" => "Các: thuộc tính phải có nhiều hơn: các mục giá trị.",
        "file" => "Các: thuộc tính phải lớn hơn: giá trị kilobyte.",
        "numeric" => "Các: thuộc tính phải lớn hơn: Giá trị.",
        "string" => "Các: thuộc tính phải lớn hơn: ký tự giá trị."
    ],
    "gte" => [
        "array" => "Các: thuộc tính phải có: Các mục giá trị hoặc nhiều hơn.",
        "file" => "Các: thuộc tính phải lớn hơn hoặc bằng: Giá trị kilobyte.",
        "numeric" => "Các: thuộc tính phải lớn hơn hoặc bằng: Giá trị.",
        "string" => "Các: thuộc tính phải lớn hơn hoặc bằng: các ký tự giá trị."
    ],
    "image" => "Các: thuộc tính phải là một hình ảnh.",
    "in" => "Các thuộc tính đã chọn: không hợp lệ.",
    "in_array" => "Trường: thuộc tính không tồn tại trong: Khác.",
    "integer" => "Các: thuộc tính phải là một số nguyên.",
    "ip" => "Các: thuộc tính phải là một địa chỉ IP hợp lệ.",
    "ipv4" => "Các: thuộc tính phải là một địa chỉ IPv4 hợp lệ.",
    "ipv6" => "Các: thuộc tính phải là một địa chỉ IPv6 hợp lệ.",
    "json" => "Các: thuộc tính phải là một chuỗi JSON hợp lệ.",
    "lt" => [
        "array" => "Các: thuộc tính phải có ít hơn: các mục giá trị.",
        "file" => "Các: thuộc tính phải nhỏ hơn: Giá trị kilobyte.",
        "numeric" => "Các: thuộc tính phải nhỏ hơn: Giá trị.",
        "string" => "Các: thuộc tính phải nhỏ hơn: ký tự giá trị."
    ],
    "lte" => [
        "array" => "Các: Thuộc tính không được có nhiều hơn: Giá trị mục.",
        "file" => "Các: thuộc tính phải nhỏ hơn hoặc bằng: giá trị kilobyte.",
        "numeric" => "Các: thuộc tính phải nhỏ hơn hoặc bằng: Giá trị.",
        "string" => "Các: thuộc tính phải nhỏ hơn hoặc bằng: các ký tự giá trị."
    ],
    "max" => [
        "array" => "Các: thuộc tính có thể không có nhiều hơn: mục tối đa.",
        "file" => "Các: Thuộc tính có thể không lớn hơn: Max Kilobyte.",
        "numeric" => "Các: thuộc tính có thể không lớn hơn: Max.",
        "string" => "Các: thuộc tính có thể không lớn hơn: các ký tự MAX."
    ],
    "mimes" => "Các: thuộc tính phải là một tệp loại :: Giá trị.",
    "mimetypes" => "Các: thuộc tính phải là một tệp loại :: Giá trị.",
    "min" => [
        "array" => "Các: thuộc tính phải có ít nhất: các mục tối thiểu.",
        "file" => "Các: thuộc tính ít nhất là: Min Kilobyte.",
        "numeric" => "Các: thuộc tính phải ít nhất: tối thiểu.",
        "string" => "Các: thuộc tính phải có ít nhất: các ký tự MIN."
    ],
    "not_in" => "Các thuộc tính đã chọn: không hợp lệ.",
    "not_regex" => "Các định dạng: thuộc tính không hợp lệ.",
    "numeric" => "Các: thuộc tính phải là một số.",
    "password" => "Mật khẩu không đúng.",
    "present" => "Trường: thuộc tính phải có mặt.",
    "regex" => "Các định dạng: thuộc tính không hợp lệ.",
    "required" => "Trường: thuộc tính là bắt buộc.",
    "required_if" => "Trường: thuộc tính là bắt buộc khi: Khác là: Giá trị.",
    "required_unless" => "Trường: thuộc tính là bắt buộc trừ khi: Khác là trong: Giá trị.",
    "required_with" => "Trường: thuộc tính là bắt buộc khi: Giá trị có mặt.",
    "required_with_all" => "Trường: thuộc tính là bắt buộc khi: Giá trị có mặt.",
    "required_without" => "Trường: thuộc tính là bắt buộc khi: không có giá trị.",
    "required_without_all" => "Trường: thuộc tính là bắt buộc khi không có giá trị nào: có mặt.",
    "same" => "Các: thuộc tính và: Khác phải khớp.",
    "size" => [
        "array" => "Các: thuộc tính phải chứa: Các mục kích thước.",
        "file" => "Các: Thuộc tính phải là: Kích thước kilobyte.",
        "numeric" => "Các: thuộc tính phải là: kích thước.",
        "string" => "Các: thuộc tính phải là: Kích thước ký tự."
    ],
    "starts_with" => "Các: thuộc tính phải bắt đầu bằng một trong các thao tác sau :: Giá trị",
    "string" => "Các: thuộc tính phải là một chuỗi.",
    "timezone" => "Các: thuộc tính phải là một vùng hợp lệ.",
    "unique" => "Các: thuộc tính đã được thực hiện.",
    "uploaded" => "Các: thuộc tính không thể tải lên.",
    "url" => "Các định dạng: thuộc tính không hợp lệ.",
    "uuid" => "Các: thuộc tính phải là một UUID hợp lệ."
];
