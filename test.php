<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>店舗レビューと情報</title>
    <link rel="stylesheet" href="test.css">
    
</head>
<body>

<div class="container">
    <img src="image_path" alt="店舗画像" class="store-image">
    
    <div class="center-button">
        <button id="openModalBtn" class="btn-primary">レビューを投稿</button>
    </div>

    <!-- モーダル -->
    <div id="myModal" class="modal">
        <!-- モーダルコンテンツ -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="container">
                <h2>レビュー投稿フォーム</h2>
                <form id="reviewForm" action="" method="POST">
                    <div class="form-group">
                        <label for="name">名前:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="date">日付:</label>
                        <input type="text" id="date" name="date" placeholder="YYYY-MM-DD" required>
                    </div>
                    <div class="form-group">
                        <label>評価:</label>
                        <div class="rating">
                            <input type="radio" id="star5" name="rating" value="5">
                            <label for="star5">★</label>
                            <input type="radio" id="star4" name="rating" value="4">
                            <label for="star4">★</label>
                            <input type="radio" id="star3" name="rating" value="3">
                            <label for="star3">★</label>
                            <input type="radio" id="star2" name="rating" value="2">
                            <label for="star2">★</label>
                            <input type="radio" id="star1" name="rating" value="1">
                            <label for="star1">★</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="review">レビュー内容:</label>
                        <textarea id="review" name="review" rows="4" required></textarea>
                    </div>

                    <input type="submit" class="btn btn-primary" value="投稿">
                </form>
            </div>
        </div>
    </div>

    <div class="sort-options">
        <label for="sort">評価のソート順:</label>
        <select id="sort" onchange="sortReviews()">
            <option value="desc">高い順</option>
            <option value="asc">低い順</option>
        </select>
    </div>

    <div class="review-list" id="reviewList">
        <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
        <h2>投稿されたレビュー</h2>
        <?php
        $dsn = 'mysql:host=localhost;dbname=ocs;charset=utf8';
        $user = 'root';
        $password = '';
        $db = new PDO($dsn, $user, $password);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
            $name = $_POST['name'];
            $date = $_POST['date'];
            $review = $_POST['review'];
            $rating = $_POST['rating'];

            if (!empty($rating)) {
                $stmt = $db->prepare("INSERT INTO reviews (name, date, evaluation, review) VALUES (:name, :date, :evaluation, :review)");
                $stmt->bindValue(':name', $name);
                $stmt->bindValue(':date', $date);
                $stmt->bindValue(':evaluation', $rating);
                $stmt->bindValue(':review', $review);

                $stmt->execute();
            } else {
                echo "評価を選択してください。";
            }
        }

        function fetchReviews($order) {
            global $db;
            $stmt = $db->prepare("SELECT name, date, evaluation, review FROM reviews ORDER BY evaluation " . $order);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $order = isset($_GET['sort']) && in_array($_GET['sort'], ['asc', 'desc']) ? $_GET['sort'] : 'desc';
        $reviews = fetchReviews($order);

        foreach ($reviews as $review) {
            echo '<div class="review-item">';
            echo '<div class="name">' . htmlspecialchars($review['name']) . '</div>';
            echo '<div class="date">' . htmlspecialchars($review['date']) . '</div>';
            echo '<div class="rating">' . str_repeat('★', $review['evaluation']) . str_repeat('☆', 5 - $review['evaluation']) . '</div>';
            echo '<div class="review-text">' . nl2br(htmlspecialchars($review['review'])) . '</div>';
            echo '</div>';
        }
        ?>

        <hr> <!-- レビューと店舗情報の区切り -->

        <?php
        try {
            // データベースから店舗の情報を取得
            $stmt = $db->query('SELECT name, holiday, distance, time, amount, review, menu, image_path FROM store');
            $store = $stmt->fetch(PDO::FETCH_ASSOC); // 最初の店舗情報を取得

            // 取得した値をHTMLで出力
            echo '<div class="store-info">';
            echo '<div class="store-details">';
            echo '<button class="Button" onclick="location.href=\'map.php\'">マップに戻る</button>';
            echo '<a id="name" href="xxxx.html">' . htmlspecialchars($store['name'], ENT_QUOTES, 'UTF-8') . '</a>';
            echo '<p id="hyouka">評価</p>';
            echo '<p id="day">定休日: ' . htmlspecialchars($store['holiday'], ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<p id="distance">学校からの距離: ' . htmlspecialchars($store['distance'], ENT_QUOTES, 'UTF-8') . ' km</p>';
            echo '<p id="time">学校からの時間: ' . htmlspecialchars($store['time'], ENT_QUOTES, 'UTF-8') . ' 分</p>';
            echo '<p id="money">平均金額: ' . htmlspecialchars($store['amount'], ENT_QUOTES, 'UTF-8') . ' 円</p>';
            echo '<p id="comment">製作者の一言: ' . htmlspecialchars($store['review'], ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<p id="menu">オススメのメニュー: ' . htmlspecialchars($store['menu'], ENT_QUOTES, 'UTF-8') . '</p>';
            echo '</div>';
            echo '<img src="' . htmlspecialchars($store['image_path'], ENT_QUOTES, 'UTF-8') . '" alt="店舗画像" class="store-image">';

            echo '</div>';
        } catch (PDOException $e) {
            echo '接続エラー: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
        ?>

    </div>
</div>

<script>
    // モーダルを取得
    var modal = document.getElementById("myModal");

    // モーダルを開くボタンを取得
    var btn = document.getElementById("openModalBtn");

    // モーダルを閉じる<span>要素を取得
    var span = document.getElementsByClassName("close")[0];

    // ボタンがクリックされた時にモーダルを開く
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // <span> (x)がクリックされた時にモーダルを閉じる
    span.onclick = function() {
        modal.style.display = "none";
    }

    // モーダルの外側がクリックされた時にモーダルを閉じる
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // レビューをソートする関数
    function sortReviews() {
        var sortValue = document.getElementById("sort").value;
        window.location.href = window.location.pathname + '?sort=' + sortValue;
    }

    // URLパラメータに基づいて選択されたソートオプションを設定
    window.onload = function() {
        var urlParams = new URLSearchParams(window.location.search);
        var sortParam = urlParams.get('sort');
        if (sortParam) {
            document.getElementById("sort").value = sortParam;
        }
    }
</script>

</body>
</html>
