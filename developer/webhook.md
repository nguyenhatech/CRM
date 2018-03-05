
## 1. Tài khoản API

### 1.1.Lấy API Key
Truy cập vào: http://crm.havaz.vn sau đó vào trang:
![enter image description here](http://apicrm.havaz.vn/images/developer.png)

Tại trang **Kết nối tài khoản developers** click nút **Generate**, hệ thống sẽ tự động sinh Client ID và Client Secret cho bạn. Thông tin này được sử dụng để lấy token.

Hãy bảo mật kỹ thông tin Client Secret, đó là thông tin quan trọng trong việc xác thực thông tin giao dịch và khách hàng  từ phía bạn.

### 1.2.Lấy token

Để lấy token, bạn cần thực hiện một POST request như sau:

**POST** `https://apicrm.havaz.vn/api/v2/login`

Thiết lập thông tin header:

    Accept: application/json
    Content-Type: application/json

Request body:

    {
            "username": "test@gmail.com",
            "password": "123456",
            "client_id": 1,
            "client_secret": "gNBVFAvvU7lp9ARkqh7M0VOz..."
    }

Thông tin request như sau:

- `username`: Tài khoản developer mà bạn vừa đăng ký
- `password`: Password của tài khoản developer mà bạn vừa đăng ký
- `client_id`: Thông tin Client ID lấy được trong mục API
- `client_secret`: Thông tin Client Secret lấy được trong mục API

### 1.3.Xác thực token

Hầu hết các api của Hải Vân CRM được bảo vệ bởi hệ thống xác thực token. Một khi chúng ta đã có được token thì chúng ta cần phải đính kèm token vào mỗi request thông qua một header Authentication kiểu Bearer.

    Accept: application/json
    Content-Type: application/json
    Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbG.....

Mặc định, thời gian tồn tại của token là rất dài nên bạn có thể lưu vào đâu đó (local storage, file...) để tiện cho việc thực hiện các request tiếp theo.


### 1.4.Thông báo lỗi

Nếu request tới 1 api mà không có thông tin xác thực, bạn sẽ nhận được 1 response Unauthenticated như sau:

    {
        "code": 401,
        "status": "error",
        "data": {
            "error": "Unauthenticated."
        }
    }

Hãy chắc chắn là bạn đã thêm một header Authorization cho các request.

### 1.5.Lấy danh sách mã khuyến mại

**GET** `https://apicrm.havaz.vn/api/v2/promotions`

Thiết lập thông tin header:

    Accept: application/json
    Content-Type: application/json

Response:

    {
        "code": 200,
        "status": "success",
        "data": [
            {
                "id": 2,
                "client_id": 2,
                "code": "OHOHOH",
                "type": 1,
                "type_txt": "%",
                "image": "2018_01_30_0d33ca9ee0c07e24bf8c7733c3696f45.jpg",
                "image_path": "http:\/\/crm.test\/storage\/images\/promotions\/2018_01_30_0d33ca9ee0c07e24bf8c7733c3696f45.jpg",
                "title": "khuyen mai",
                "description": "day la mo ta",
                "content": "day la noi dung",
                "amount": 20,
                "amount_max": 0,
                "quantity": 0,
                "quantity_per_user": 0,
                "date_start": "2018-01-05 15:15:15",
                "date_end": "2018-01-20 20:20:20",
                "status": 1,
                "status_txt": "Đã kích hoạt",
                "created_at": "2018-03-02 09:20:20"
            }
        ],
        "meta": {
            "pagination": {
                "total": 1,
                "count": 1,
                "per_page": 25,
                "current_page": 1,
                "total_pages": 1,
                "links": []
            }
        }
    }
Lấy tất cả khuyến mại không phân trang:

**GET** `https://apicrm.havaz.vn/api/v2/promotions?limit=-1`


### 1.6.Tạo mã khuyến mại

**POST** `https://apicrm.havaz.vn/api/v2/promotions`

Thiết lập thông tin header:

    Accept: application/json
    Content-Type: application/json
Request body:

    {
        "id": 1,
        "code": "OHOHOH",
        "title": "khuyen mai",
        "type": 1,
        "image": "https://your_url.vn/2018_01_30_0d33ca9ee0c07e24bf8c7733c3696f45.jpg",
        "description": "day la mo ta",
        "content": "day la noi dung",
        "amount": 20,
        "amount_max": 0,
        "amount_segment": 0,
        "quantity": 10,
        "quantity_per_user": 10,
        "date_start": "2018-01-05 6:00:00",
        "date_end": "2018-01-20 6:00:00",
        "status": 1
    }

Thông tin request như sau:
- `id`: Mã hệ thống
- `code`: Mã khuyến mại  (required|max:50)
- `title`: Tiêu đề  (required)
- `type`: Loại khuyến mại  (required|numeric)
- `image`: Đường dẫn ảnh khuyến mại của bạn  khuyến mại
- `description`: Mô tả
- `content`: Nội dung
- `amount`: Tiền giảm cả tuyến  (required|numeric|min:0)
- `amount_max`: Tiền giảm tối đa (nullable|numeric|min:0)
- `amount_segment`: Tiền giảm theo chặng ( nullable|numeric|min:0)
- `quantity`: Số lượt dùng (Mặc định không giới hạn)   ( nullable|numeric|min:0)
- `quantity_per_user`: Số lượt dùng tối đa mỗi khách (Mặc định không giới hạn)  ( nullable|numeric|min:0)
- `date_start`: Ngày giờ bắt đầu áp dụng khuyến mại (required|date_format:Y-m-d H:i:s)
- `date_end`: Ngày giờ kết thúc áp dụng khuyến mại (required|date_format:Y-m-d H:i:s)
- `status`: Trạng thái khuyến mại (nullable|numeric)

Loại khuyến mại `type`
``` php
    [
        0: "VNĐ" // Tiền mặt
        1: "%" // Phần trăm
    ]
```
Trạng thái khuyến mại `status`
``` php
    [
        0: "Chưa kích hoạt"
        1: "Đã kích hoạt"
    ]
```

### 1.7.Cập nhật mã khuyến mại

**PUT** `https://apicrm.havaz.vn/api/v2/promotions/{id}`

Thiết lập thông tin header:

```
Accept: application/json
Content-Type: application/json

```

Request body:

```
{
    "code": "OHOHOH",
    "title": "khuyen mai",
    "type": 1,
    "image": "https://your_url.vn/2018_01_30_0d33ca9ee0c07e24bf8c7733c3696f45.jpg",
    "description": "day la mo ta",
    "content": "day la noi dung",
    "amount": 20,
    "amount_max": 0,
    "amount_segment": 0,
    "quantity": 10,
    "quantity_per_user": 10,
    "date_start": "2018-01-05 6:00:00",
    "date_end": "2018-01-20 6:00:00",
    "status": 1
}
```
### 1.8.Cập nhật trạng thái mã khuyến mại

**POST** `https://apicrm.havaz.vn/api/v2/promotions/{id}/active`

Trạng thái khuyến mại `status`
``` php
    [
        0: "Chưa kích hoạt"
        1: "Đã kích hoạt"
    ]
```

Thiết lập thông tin header:

```
Accept: application/json
Content-Type: application/json
```
Request body:

```
{
    "status": 1,
}
```

### 1.9.Xóa mã khuyến mại

**DELETE** `https://apicrm.havaz.vn/api/v2/promotions/{id}`

Thiết lập thông tin header:

```
Accept: application/json
Content-Type: application/json
```

## 2. Webhooks
Để có được hiệu năng tốt nhất, Hải Vân CRM sử dụng webhook để đẩy thông tin theo phương thức bất đồng bộ thông qua việc listen các event trên tài khoản Hải Vân CRM của bạn.

### 2.1. Danh sách các webhook events:

Bạn có thể sử dụng các webhook event dưới đây cho việc cập nhật và lưu trữ thông tin của mình:

- Thông tin giao dịch
- Khách hàng

Các webhook đều sử dụng phương thức **POST** để push dữ liệu.

### 2.2. Cài đặt webhook

Truy cập vào trang **Developer** bạn chọn Webhook event và điền 1 endpoint để listen event này. Sau đó chọn **Add**.

Lưu ý:

- Bạn nên sử dụng 1 secured url (https) để listen webhook event.
- Bạn có thể sử dụng nhiều endpoint để listen 1 webhook event.

### 2.3. Xác thực webhook

Mỗi webhook request sẽ bao gồm 1 `HTTP_X_CRM_HMAC_SHA256` trong header. Nó được sinh ra từ việc mã hóa thông tin `client_secret` của bạn. Do vậy, hãy chú ý tới vấn đề bảo mật thông tin nếu bạn không muốn bị fake dữ liệu nhận được.

Để xác thực một webhook request từ Hải Vân CRM, bạn sẽ cần mã hóa các thông tin mà bạn có, sau đó so sánh với chuỗi giá trị của `HTTP_X_CRM_HMAC_SHA256`. Nếu chúng khớp với nhau, bạn có thể sử dụng thông tin từ body request để cập nhật hoặc lưu trữ.

Code mẫu về việc mã hóa tạo ra chuỗi so sánh với HMAC của Hải Vân CRM (PHP):

    <?php
            define('CLIENT_SECRET', 'bestshop_secret');

            function verify_webhook($data, $webhook_hmac){
                    $compared_hmac = base64_encode(hash_hmac('sha256', json_encode($data), CLIENT_SECRET, true));
                    return ($webhook_hmac == $compared_hmac);
            }

            $webhook_hmac = $_SERVER['HTTP_X_CRM_HMAC_SHA256'];
            $data = json_decode(file_get_contents('php://input'), true);
            $verified = verify_webhook($data, $webhook_hmac);

            if ($verified) {
                // Update or insert
            }
    ?>

### 2.4. Nhận thông tin từ webhook

Sau khi bạn đã đăng ký thông tin nhận webhook thành công, Hải Vân CRM sẽ gửi các request mang thông tin tương ứng của webhook event theo phương thức POST tới cho bạn mỗi khi có event xảy ra.

Với mỗi event, dữ liệu request tới sẽ là khác nhau. Sau đây là các mẫu request theo webhook event:

**2.4.1. Thông tin giao dịch**

    {
        "code": 200,
        "status": "success",
        "data": {
            "uuid": "lqjz2jm4",
            "customer_id": 1,
            "description": "Mang tiền đi chơi",
            "total_amount": "-120000",
            "total_point": 120,
            "name": "Lê Bảo Khang",
            "phone": "0988372912",
            "email": "lekhang2512@gmail.com",
            "status": 1,
            "webhook_type": 1,
            "payment_at": "2018-03-02"
        }
    }

Trong đó:

- `uuid`: Mã thông tin giao dịch Hải Vân CRM
- `customer_id`: Mã khách hàng của giao dịch
- `description`: Mô tả giao dịch
- `total_amount`: Tổng số tiền giao dịch
- `total_point`: Tổng số điểm giao dịch
- `name`: Tên khách hàng
- `phone`: Số điện thoại của khách hàng
- `email`: Email của khách hàng
- `status`: Trạng thái giao dịch
- `webhook_type`: Loại Webhook
- `payment_at`: Ngày giao dịch

Các trạng thái `status`
``` php
    [
        0: "Chờ giao dịch",
        1: "Giao dịch thành công",
        2: "Giao dịch bị hủy"
    ]
```
Loại Webhook `webhook_type`
``` php
    [
        1: "Thông tin giao dịch"
        2: "Khách hàng"
    ]
```


Khi bạn nhận được các thông tin này, bạn có thể cập nhật lại thông tin giao dịch cho chính xác.

**2.4.2. Khách hàng**

    {
        "code": 200,
        "status": "success",
        "data": {
            "uuid": "p5jnovbm",
            "name": "King Hacker1",
            "phone": "09882313121",
            "email": "king12@gmail.com",
            "address": "102 thái thịnh đống đa hà nội",
            "level": null,
            "avatar": null,
            "avatar_path": "http:\/\/crm.test\/avatar_default.png",
            "webhook_type": 2
        }
    }


Trong đó:

- `uuid`: Mã khách hàng
- `name`: Tên khách hàng
- `phone`: Số điện thoại của khách hàng
- `email`: Email của khách hàng
- `address`: Địa chỉ khách hàng
- `level`: Cấp độ khách hàng
- `avatar`: Ảnh đại diện khách hàng
- `avatar_path`: Đường dẫn ảnh đại diện khách hàng
- `webhook_type`: Loại Webhook

Khi bạn nhận được các thông tin này, bạn có thể cập nhật lại thông tin khách hàng cho chính xác.


### 3. Phản hồi lại webhook

Bất cứ khi nào bạn nhận được webhook request từ Hải Vân CRM, hãy phản hồi lại với 1 HTTP status code là `200` OK. Bất kỳ HTTP status code không phải `200` đều bị Hải Vân CRM coi là chưa nhận được dữ liệu. Như thế, Hải Vân CRM sẽ thử gửi lại thông tin webhook cho bạn.

Sau 3 lần không thể gửi được, nó sẽ không thử gửi thêm nữa.
