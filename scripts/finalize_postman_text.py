import json
from pathlib import Path


path = Path("Restaurant API.postman_collection.json")
data = json.loads(path.read_text(encoding="utf-8"))


def folder(items, name):
    return next(item for item in items if item["name"] == name)


def request(parent, name):
    return next(item for item in parent["item"] if item["name"] == name)


auth = folder(data["item"], "Auth")
request(auth, "Login")["request"]["description"] = "Đăng nhập bằng `username` hoặc `email`."
request(auth, "Refresh Token")["request"]["description"] = "Làm mới token. Có thể gửi kèm Bearer token hiện tại."
request(auth, "Me")["request"]["description"] = "Lấy thông tin tài khoản đang đăng nhập."
request(auth, "Logout")["request"]["description"] = "Đăng xuất tài khoản hiện tại."
auth["description"] = "API xác thực: login, refresh, me, logout."

users = folder(data["item"], "Users")
request(users, "List Users")["request"]["description"] = "Lấy danh sách user. Route hiện yêu cầu role `MANAGER`."
request(users, "Show User")["request"]["description"] = "Lấy chi tiết user theo id."
request(users, "Create User")["request"]["description"] = "Tạo user mới."
request(users, "Update User")["request"]["description"] = "Cập nhật user theo id."
request(users, "Delete User")["request"]["description"] = "Ẩn user bằng `is_active = false`."
request(users, "Create User")["request"]["body"]["raw"] = """{
  "user_name": "manager_02",
  "full_name": "Quản lý ca tối",
  "email": "manager02@example.com",
  "password": "12345678",
  "role_id": {{role_id}}
}"""
request(users, "Update User")["request"]["body"]["raw"] = """{
  "full_name": "Quản lý ca tối - cập nhật",
  "email": "manager02.updated@example.com",
  "is_active": true
}"""
users["description"] = """API người dùng. Xóa là soft delete.

## Cấu trúc dữ liệu bảng `users`

| Tên cột | Kiểu dữ liệu | Ghi chú |
| --- | --- | --- |
| `id` | bigint | Khóa chính của người dùng. |
| `role_id` | bigint | Khóa ngoại liên kết tới bảng `roles`, xác định vai trò của user. |
| `user_name` | varchar(255) | Tên đăng nhập duy nhất, có thể dùng để login. |
| `full_name` | varchar(255) | Họ tên hiển thị của người dùng. |
| `email` | varchar(255) | Email đăng nhập/liên hệ, duy nhất trong hệ thống. |
| `password` | varchar(255) | Mật khẩu đã được hash. |
| `is_active` | boolean | Trạng thái hoạt động; `false` nghĩa là đã bị ẩn/khóa mềm. |
| `created_at` | timestamp | Thời điểm tạo bản ghi. |
| `updated_at` | timestamp | Thời điểm cập nhật gần nhất. |
"""

roles = folder(data["item"], "Roles")
request(roles, "List Roles")["request"]["description"] = "Lấy danh sách vai trò."
request(roles, "Show Role")["request"]["description"] = "Lấy chi tiết vai trò theo slug."
request(roles, "Create Role")["request"]["description"] = "Tạo vai trò mới."
request(roles, "Update Role")["request"]["description"] = "Cập nhật vai trò theo slug."
request(roles, "Delete Role")["request"]["description"] = "Ẩn vai trò bằng `is_active = false`."
request(roles, "Create Role")["request"]["body"]["raw"] = """{
  "name": "Giám sát",
  "slug": "SUPERVISOR",
  "remark": "Giám sát vận hành trong ca"
}"""
request(roles, "Update Role")["request"]["body"]["raw"] = """{
  "name": "Giám sát ca",
  "remark": "Vai trò giám sát ca làm việc",
  "is_active": true
}"""
roles["description"] = """API vai trò. Xóa là soft delete. Detail/update/delete dùng slug.

## Cấu trúc dữ liệu bảng `roles`

| Tên cột | Kiểu dữ liệu | Ghi chú |
| --- | --- | --- |
| `id` | bigint | Khóa chính của vai trò. |
| `name` | varchar(255) | Tên hiển thị của vai trò, ví dụ Quản lý, Thu ngân. |
| `slug` | varchar(255) | Mã định danh duy nhất dùng trong API detail/update/delete. |
| `remark` | varchar(255), nullable | Ghi chú mô tả trách nhiệm hoặc phạm vi vai trò. |
| `is_active` | boolean | Trạng thái hoạt động; `false` là ẩn mềm. |
| `created_at` | timestamp | Thời điểm tạo bản ghi. |
| `updated_at` | timestamp | Thời điểm cập nhật gần nhất. |
"""

path.write_text(json.dumps(data, ensure_ascii=False, indent=4), encoding="utf-8")
print("Finalized Postman Vietnamese text.")
