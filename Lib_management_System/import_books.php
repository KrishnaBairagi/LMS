<?php
include 'config/db.php';

// Sample books data
$books = [
    ['The Great Gatsby', 'F. Scott Fitzgerald', 'Fiction', 5, 'gatsby.jpg'],
    ['To Kill a Mockingbird', 'Harper Lee', 'Fiction', 4, 'mockingbird.jpg'],
    ['1984', 'George Orwell', 'Dystopian', 6, '1984.jpg'],
    ['Pride and Prejudice', 'Jane Austen', 'Romance', 7, 'pride.jpg'],
    ['The Catcher in the Rye', 'J.D. Salinger', 'Fiction', 3, 'catcher.jpg'],
    ['Atomic Habits', 'James Clear', 'Self-Help', 8, 'atomic.jpg'],
    ['Thinking, Fast and Slow', 'Daniel Kahneman', 'Psychology', 5, 'thinking.jpg'],
    ['Sapiens', 'Yuval Noah Harari', 'History', 6, 'sapiens.jpg'],
    ['The Lean Startup', 'Eric Ries', 'Business', 4, 'lean.jpg'],
    ['Educated', 'Tara Westover', 'Biography', 5, 'educated.jpg'],
    ['The Silent Patient', 'Alex Michaelides', 'Thriller', 4, 'silent.jpg'],
    ['Become Your Best Self', 'Jay Shetty', 'Motivation', 7, 'bestself.jpg'],
    ['Dune', 'Frank Herbert', 'Science Fiction', 5, 'dune.jpg'],
    ['The Hobbit', 'J.R.R. Tolkien', 'Fantasy', 6, 'hobbit.jpg'],
    ['Harry Potter and the Philosopher\'s Stone', 'J.K. Rowling', 'Fantasy', 10, 'hp1.jpg'],
    ['The Lord of the Rings', 'J.R.R. Tolkien', 'Fantasy', 4, 'lotr.jpg'],
    ['Goosebumps', 'R.L. Stine', 'Horror', 8, 'goosebumps.jpg'],
    ['The Da Vinci Code', 'Dan Brown', 'Mystery', 5, 'davinci.jpg'],
    ['Angels & Demons', 'Dan Brown', 'Mystery', 4, 'angels.jpg'],
    ['Inferno', 'Dan Brown', 'Thriller', 3, 'inferno.jpg'],
    ['The Alchemist', 'Paulo Coelho', 'Fiction', 9, 'alchemist.jpg'],
    ['The Midnight Library', 'Matt Haig', 'Fantasy', 6, 'midnight.jpg'],
    ['Where the Crawdads Sing', 'Delia Owens', 'Fiction', 5, 'crawdads.jpg'],
    ['A Brief History of Time', 'Stephen Hawking', 'Science', 4, 'history_time.jpg'],
    ['Python Crash Course', 'Eric Matthes', 'Programming', 8, 'python.jpg'],
    ['Clean Code', 'Robert C. Martin', 'Programming', 5, 'clean.jpg'],
    ['Design Patterns', 'Gang of Four', 'Programming', 3, 'patterns.jpg'],
    ['The Pragmatic Programmer', 'Hunt & Thomas', 'Programming', 4, 'pragmatic.jpg'],
    ['Code Complete', 'Steve McConnell', 'Programming', 3, 'code.jpg'],
    ['Introduction to Algorithms', 'CLRS', 'Technology', 2, 'algorithms.jpg'],
];

$imported = 0;
$failed = 0;

foreach ($books as $book) {
    $title = $book[0];
    $author = $book[1];
    $category = $book[2];
    $quantity = $book[3];
    $image = $book[4];
    
    $query = "INSERT INTO books (title, author, category, quantity, available, image) 
              VALUES ('$title', '$author', '$category', '$quantity', '$quantity', '$image')";
    
    if ($conn->query($query)) {
        $imported++;
    } else {
        $failed++;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h2 class="mb-4">✅ Import Successful!</h2>
                        <div class="alert alert-success mb-3">
                            <h5><?= $imported ?> books imported successfully</h5>
                        </div>
                        <?php if ($failed > 0): ?>
                            <div class="alert alert-warning">
                                <p><?= $failed ?> books failed to import</p>
                            </div>
                        <?php endif; ?>
                        <a href="books.php" class="btn btn-primary btn-lg">
                            📚 View Books
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
