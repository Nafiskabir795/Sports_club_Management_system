# Sports Club — Viva & Fundamentals Guide

## 1. Project architecture

The project uses **HTML + CSS + JavaScript + PHP + MySQL**.

Typical request flow:

`HTML form → JavaScript event → fetch() → PHP endpoint → PDO → MySQL → JSON response → JavaScript → DOM`

- **HTML**: structure, forms, buttons, tables and page content.
- **CSS**: layout, spacing, colors, typography, responsive design and themes.
- **JavaScript**: events, DOM manipulation, client-side validation and API calls.
- **PHP**: server-side validation, authentication, sessions and database operations.
- **MySQL**: persistent relational data.
- **PDO**: PHP database access layer.
- **JSON**: data exchanged between browser and PHP.

## 2. HTML essentials

```html
<h1>Heading</h1>
<p>Paragraph</p>
<a href="page.html">Link</a>
<button>Click</button>
<form>...</form>
<input type="text">
<select><option>Football</option></select>
```

### `id` vs `class`

```html
<div id="profile">...</div>
<div class="card">...</div>
<div class="card">...</div>
```

- `id` normally identifies one specific element.
- `class` can be reused.

JavaScript:

```js
document.getElementById("profile");
document.querySelector(".card");
```

## 3. CSS essentials

### Text and background

```css
color: red;
background-color: black;
font-family: Arial, sans-serif;
font-size: 20px;
font-weight: bold;
```

### Spacing

```css
padding: 20px; /* inside */
margin: 20px;  /* outside */
gap: 20px;     /* space between flex/grid items */
```

### Center text

```css
text-align: center;
```

### Center a button with Flexbox

```css
.container {
    display: flex;
    justify-content: center;
}
```

Horizontal + vertical:

```css
.container {
    display: flex;
    justify-content: center;
    align-items: center;
}
```

Or:

```css
.container {
    display: grid;
    place-items: center;
}
```

For a fixed-width block:

```css
button {
    display: block;
    width: 200px;
    margin: 0 auto;
}
```

### Box model

`content → padding → border → margin`

The project uses:

```css
* {
    box-sizing: border-box;
}
```

which makes declared width/height include padding and border.

### Flexbox

```css
display: flex;
flex-direction: row;
justify-content: center;
align-items: center;
gap: 20px;
```

### Grid

```css
display: grid;
grid-template-columns: repeat(3, 1fr);
gap: 20px;
```

## 4. JavaScript essentials

```js
const name = "Ali";
let age = 20;

function add(a, b) {
    return a + b;
}

if (age >= 18) {
    console.log("Adult");
}
```

Arrays:

```js
const sports = ["Football", "Cricket", "Volleyball"];
```

Objects:

```js
const student = {
    name: "Ali",
    age: 20
};
```

### DOM

```js
const button = document.getElementById("loginButton");
button.textContent = "Login";
button.classList.add("active");
```

### Events

```js
button.addEventListener("click", function () {
    alert("Clicked");
});
```

Form:

```js
form.addEventListener("submit", function (event) {
    event.preventDefault();
});
```

`preventDefault()` stops the browser's normal form action so JavaScript can handle it.

### Fetch/API

```js
const response = await fetch("backend/login.php", {
    method: "POST",
    body: JSON.stringify({
        identifier: "ST001",
        password: "secret"
    })
});
```

- GET is commonly used to retrieve data.
- POST is commonly used to send data to the server.

### JSON

```js
JSON.stringify(object);
JSON.parse(text);
```

## 5. PHP essentials

```php
$name = "Ali";

if ($name === "Ali") {
    echo "Hello";
}

$data = [
    'name' => 'Ali',
    'age' => 20
];

echo $data['name'];
```

Functions:

```php
function greet(string $name): string
{
    return "Hello " . $name;
}
```

Useful superglobals:

- `$_POST` — POST form/request data.
- `$_GET` — URL query data.
- `$_SESSION` — server-side session data.
- `$_SERVER` — request/server information.

## 6. Sessions and authentication

A session lets the server remember a user's state between HTTP requests.

This project stores information such as:

```php
$_SESSION['authenticated'] = true;
$_SESSION['user_id'] = ...;
$_SESSION['role'] = ...;
```

**Authentication** asks: "Who are you?"

**Authorization** asks: "What are you allowed to do?"

## 7. PHP + MySQL with PDO

Conceptually:

```php
$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
```

PDO provides the database interface.

Important configuration used by this project includes exception-based error reporting and real prepared statements.

### INSERT

```sql
INSERT INTO students
(student_code, name, phone, email)
VALUES
('ST001', 'Ali', '01700000000', 'ali@example.com');
```

Safer PHP pattern:

```php
$stmt = $pdo->prepare(
    "INSERT INTO students (student_code, name, phone, email)
     VALUES (:student_code, :name, :phone, :email)"
);

$stmt->execute([
    ':student_code' => $studentCode,
    ':name' => $name,
    ':phone' => $phone,
    ':email' => $email
]);
```

Prepared statements separate SQL structure from values and help prevent SQL injection.

## 8. Core SQL/DBMS

CRUD:

- **Create** → `INSERT`
- **Read** → `SELECT`
- **Update** → `UPDATE`
- **Delete** → `DELETE`

Examples:

```sql
SELECT * FROM students;

SELECT *
FROM students
WHERE student_id = 1;

UPDATE students
SET name = 'New Name'
WHERE student_id = 1;

DELETE FROM students
WHERE student_id = 1;
```

### Keys

**Primary key**: uniquely identifies a row.

```sql
student_id INT AUTO_INCREMENT PRIMARY KEY
```

**Foreign key**: references a key in another table.

```sql
FOREIGN KEY (student_id)
REFERENCES students(student_id)
```

### JOIN

```sql
SELECT s.name, sp.sport_name
FROM students s
JOIN student_sports ss
    ON s.student_id = ss.student_id
JOIN sports sp
    ON ss.sport_id = sp.sport_id;
```

A JOIN combines related rows from multiple tables.

### Constraints

- `PRIMARY KEY`
- `FOREIGN KEY`
- `NOT NULL`
- `UNIQUE`
- `DEFAULT`

### Normalization

The basic goal is to reduce unnecessary redundancy and avoid update/insert/delete anomalies.

- **1NF**: atomic values/no repeating groups.
- **2NF**: 1NF + no partial dependency on part of a composite key.
- **3NF**: 2NF + no transitive dependency.

### ACID

- **Atomicity** — all or none.
- **Consistency** — valid database state.
- **Isolation** — transactions do not improperly interfere.
- **Durability** — committed changes survive failures.

## 9. Passwords

Do not store plaintext passwords.

PHP provides:

```php
password_hash($password, PASSWORD_DEFAULT);
password_verify($password, $storedHash);
```

The database should contain a password hash rather than the original password.

## 10. Client vs server validation

Client-side JavaScript validation improves user experience.

Server-side PHP validation is still required because users can bypass JavaScript and send requests directly.

## 11. HTTP status codes to know

- `200` success
- `201` created
- `400` bad request
- `401` authentication failed/required
- `403` forbidden
- `404` not found
- `405` method not allowed
- `422` validation problem
- `500` server error

## 12. High-value viva questions

**What is HTML?**  
A markup language used to structure webpage content.

**What is CSS?**  
A stylesheet language used to control webpage presentation.

**What is JavaScript?**  
A programming language used in the browser for behavior, interaction, DOM manipulation and API communication.

**Why PHP?**  
For server-side processing, authentication, sessions, validation and database operations.

**Why MySQL?**  
It is a relational DBMS suitable for storing structured application data and relationships.

**What is PDO?**  
PHP's database-access interface used by the project to communicate with MySQL.

**How do you center a button?**  
Use Flexbox (`display:flex; justify-content:center`) on a parent, Grid (`place-items:center`), or `margin:0 auto` for a suitable block element.

**How do you change font?**  
`font-family: Arial, sans-serif;`

**How do you change text color?**  
`color: red;`

**How do you add inside space?**  
`padding`.

**How do you add outside space?**  
`margin`.

**How do you add space between flex/grid items?**  
`gap`.

**What is a primary key?**  
A unique identifier for a row.

**What is a foreign key?**  
A reference to a key in another table.

**What is SQL injection?**  
An attack where input alters the intended SQL query; parameterized/prepared statements help prevent it.

**What is CRUD?**  
Create, Read, Update, Delete.

**What is a JOIN?**  
A SQL operation that combines related rows from multiple tables.

**Authentication vs authorization?**  
Authentication identifies the user; authorization determines what that user can access.

## 13. 60-second project explanation

> Our project is a Sports Club Management System. The frontend uses HTML, CSS and JavaScript. HTML provides the structure and forms, CSS controls the layout and appearance, and JavaScript handles browser-side interaction and communicates with the PHP backend using HTTP requests and JSON. PHP handles authentication, sessions, validation and database operations using PDO. MySQL stores persistent relational data such as students, coaches, sports, teams, selections, sessions and performance records. Primary and foreign keys represent relationships, and prepared statements are used for parameterized database operations. Passwords are stored as hashes rather than plaintext.

## 14. Study strategy

For every major feature, trace:

`Button/Form → JS event → fetch() → PHP endpoint → validation → SQL/PDO → JSON response → DOM update`

You do not need to memorize every line. You should be able to explain what each layer does and why.

## 15. Practice before the viva

1. Change a button's color.
2. Change a font.
3. Center a button with Flexbox.
4. Change padding/margin/gap.
5. Find an element with `getElementById`.
6. Change `textContent`.
7. Add a click listener.
8. Read an input's `.value`.
9. Explain a `fetch()` request.
10. Write a `SELECT ... WHERE`.
11. Write an `INSERT`.
12. Write an `UPDATE`.
13. Write a `DELETE`.
14. Explain one primary-key/foreign-key relationship.
15. Explain one complete feature from browser to database.
