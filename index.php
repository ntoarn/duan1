<?php
    session_start();
    include "./model/pdo.php";
    include "./model/danhmuc.php";
    include "./model/sanpham.php";
    include "./model/binhluan.php";
    include "./model/taikhoan.php";
    include "./model/cart.php";

    if(!isset($_SESSION['mycart'])) {
        $_SESSION['mycart'] = [];
    }

    $load_danhmuc = load_dm();
    $load_sanpham = loadall_sanpham();
    $load_spcungloai = loadall_spbanchay();
    $loadall_laptop_theoten = loadall_laptop_theoten();
    $loadall_iphone_theoten = loadall_iphone_theoten();
    $loadall_pc_theoten = loadall_pc_theoten();
    
    include "./view/header.php";
    
    if(isset($_GET['act']) && ($_GET['act'] != "")) {
        $act = $_GET['act'];
        switch($act) {
            case "viewsearch":
                if(isset($_POST['submit']) && ($_POST['submit'] != "")) {
                    $tukhoa = $_POST['tukhoa'];
                    $search = $_POST['submit'];
                } else {
                    $search = "";
                }
                $search_sanpham = search_sanpham($tukhoa);
                include "./view/viewsearch.php";
                break;
            case "sanpham":
                include "./view/sanpham.php";
                break;
            case "danhmuc":
                if(isset($_GET['iddm']) && $_GET['iddm'] > 0) {
                    $iddm = $_GET['iddm'];
                } else {
                    $iddm = "";
                }
                $dssp = loadall_sanphamdanhmuc($iddm);
                $list_sanphamdanhmuc = load_spdanhmuc($iddm);
                include "./view/sanphamdanhmuc.php";
                break;
            case "sanphamct":
                if(isset($_GET['idsp']) && $_GET['idsp'] > 0) {
                    $load_sanphamct = load_sanphamct($_GET['idsp']);
                    // ở phần này bạn sẽ lấy id danh mục của bảng sản phẩm truyền vào $spcungloai
                    $spcungloai = load_spcungloai($_GET['idsp'], $load_sanphamct['iddm']);
                    $danhsachbinhluan = loadall_binhluan($_GET['idsp']);
                    include "./view/chitietsanpham.php";
                } else {
                    include "./view/home.php";
                }
                break;
            case "dangky":
                if(isset($_POST['dangky'])) {
                    $email = $_POST['email'];
                    $user = $_POST['user'];
                    $passdangky = $_POST['pass'];
                    dangky($user,$passdangky,$email);
                    $successDangky = "Chúc mừng bạn đã đăng ký thành công";
                }
                include "./view/taikhoan/dangky.php";
                break;
            case "dangnhap":
                if(isset($_POST['dangnhap'])) {
                    $user = $_POST['user'];
                    $pass = $_POST['pass'];
                    $checkuser = dangnhap($user,$pass);
                    if(is_array($checkuser)) {
                        $_SESSION['minhh'] = $checkuser;
                        // $success = "Đăng nhập thành công.";
                        // header("Location: tranvietanh_duanmau/index.php");
                    } else {
                        $success = "Thông tin tài khoản hoặc mật khẩu không chính xác";
                    }
                }
                include "./view/home.php";
                break;
            case "edit_taikhoan":
                if(isset($_POST['capnhat'])) {
                    $user = $_POST['user'];
                    $pass = $_POST['pass'];
                    $email = $_POST['email'];
                    $address = $_POST['address'];
                    $tel = $_POST['tel'];
                    $id = $_POST['id'];

                    capnhattaikhoan($user,$pass,$email,$address,$tel,$id);
                    $_SESSION['minhh'] = dangnhap($user,$pass);
                    $successEmail = "Cập nhật tài khoản thành công";
                }
                include "./view/taikhoan/edit_taikhoan.php";
                break;
            case "doimatkhau":
                if(isset($_POST['doimatkhau']) && ($_POST['doimatkhau'])) {
                    $id = $_POST['id'];
                    $passdoi = $_POST['pass'];
                    $passnew = $_POST['passnew'];
                    $errPass = $errPassnew = $successPass = "";
                    $check = true;

                    if(empty($passdoi)) {
                        $check = false;
                        $errPass = "Trường này không được để trống";
                    }

                    if(empty($passnew)) {
                        $check = false;
                        $errPassnew = "Trường này không được để trống";
                    } else {
                        if($passnew != $passdoi) {
                            $check = false;
                            $errPassnew = "Mật khẩu không khớp";
                        }
                    }

                    if($check) {
                        doimatkhau($id,$passdoi);
                        $successPass = "Đổi mật khẩu thành công";
                    }
                }
                include "view/taikhoan/doimatkhau.php";
                break;
            case "dangxuat":
                dangxuat();
                include "./view/home.php";
                break;
            case "quenmk":
                if(isset($_POST['guiemail'])) {
                    $email = $_POST['email'];
                    $sendMail = sendMail($email);
                    if($sendMail) {
                        checkemailPass($email,$sendMail['user'],$sendMail['pass']);
                        $successEmail = "Gửi email thành công bạn vui lòng kiểm tra lại email của mình";
                    } else {
                        $errEmail = "Email của bạn không tồn tại trên hệ thống";
                    }
                }
                include "./view/taikhoan/quenmk.php";
                break;
            case "addtocart":
                if(!isset($_SESSION['minhh'])) {
                    include "./view/taikhoan/dangky.php";
                } else {
                    if(isset($_POST["addtocart"]) && $_POST["addtocart"]) {
                        $id = $_POST['id'];
                        $name = $_POST['name'];
                        $img = $_POST['img'];
                        $price = $_POST['price'];
                        $soluong = 1;
                        $thanhtien = $price * $soluong;
                        // $sanphamadd = [$id,$name,$img,$price,$soluong,$thanhtien];
                        // array_push($_SESSION['mycart'],$sanphamadd);
                        // Mảng cha là $_SESSION['cart'] và mảng con là $sanphamadd truyền 2 tham số

                        $itemExists = false;
                        
                        foreach ($_SESSION['mycart'] as &$cartItem) {
                            if ($cartItem[0] == $id) {
                                // Cập nhật số lượng và tổng giá của sản phẩm đã tồn tại
                                $cartItem[4] += $soluong;
                                $itemExists = true;
                                break;
                            }
                        }

                        if (!$itemExists) {
                            // Nếu sản phẩm chưa tồn tại trong giỏ hàng, thêm nó vào giỏ
                            $sanphamadd = [$id, $name, $img, $price, $soluong, $thanhtien];
                            $_SESSION['mycart'][] = $sanphamadd;
                        }
                    }
                    include "view/giohang/giohang.php";
                }
                break;
            case "xoaspgiohang":
                if(isset($_GET['idcart'])) {
                    array_splice($_SESSION['mycart'],$_GET['idcart'],1);
                    // tham số thứ nhất truyền vào là 1 mảng, tham số thứ 2 là vị trí cần xóa, tham số thứ 3 xóa bao nhiều phần tử
                } else {
                    $_SESSION['mycart'] = [];
                }

                include "view/giohang/giohang.php";
                break;
            case "bill":
                include "view/giohang/bill.php";
                break;
            case "billconfirm":
                $idbill = null;
                if(isset($_POST["dongydathang"]) && ($_POST["dongydathang"])) {
                    if(isset($_SESSION['minhh'])) {
                        $iduser = $_SESSION['minhh']['id'];
                    } else {
                        $iduser = 0;
                    }

                    $name = $_POST['name'];
                    $email = $_POST['email'];
                    $address = $_POST['address'];
                    $tel = $_POST['tel'];
                    $pttt = $_POST['pttt'];
                    $ngaydathang = date('h:i:sa d/m/Y');
                    $tongdonhang = tongdonhang();

                    $idbill = insert_bill($iduser,$name, $email, $address, $tel, $pttt, $ngaydathang, $tongdonhang);
                    // insert into cart với $_SESSION['mycart'] và $idbil

                    foreach($_SESSION['mycart'] as $cart) {
                        insert_cart($_SESSION['minhh']['id'],$cart[0],$cart[2],$cart[1],$cart[3],$cart[4],$cart[5],$idbill);
                    }

                    $_SESSION['cart'] = [];
                }

                $listbill = loadone_bill($idbill);
                // $listbill_chitiet = loadone_cart($idbill);
                
                include "view/giohang/billconfirm.php";
                break;
            case "gioithieu":
                include "view/gioithieu.php";
                break;
        }
    } else {
        include "./view/home.php";
    }
    include "./view/footer.php";
?>