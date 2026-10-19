> [!NOTE]
> The material was created with the help of ChatGPT, Gemini, and Copilot.

# Introduction to Databases

## Why store MQTT messages?

An MQTT broker delivers messages between devices and applications. A **database keeps messages available for later use**, such as viewing history or drawing charts.

**Example flow:** **Lamp → MQTT broker → Python ingestor → SQLite database → Node.js web application.**

The **ingestor** subscribes to MQTT topics and inserts received messages into the database.

## A few database options

| Database | How it stores data | Typical use |
|---|---|---|
| **SQLite** | Tables in a single file | Small applications, Raspberry Pi, learning |
| **PostgreSQL / MySQL** | Tables managed by a database server | Applications with many users and connections |
| **MongoDB** | Flexible documents resembling JSON | Data with varying structures |
| **InfluxDB** | Time-stamped measurements | Sensor readings, monitoring, trends |

For this exercise, **SQLite is enough**: no separate database server is needed.

## SQLite basics

A **table** contains records:

- **Column:** one piece of information, such as a topic.
- **Row (record):** one stored message.
- **Primary key:** a unique identifier for each row.
- **SQL:** the language used to create tables and manage records.

Run these commands in a terminal with the SQLite command-line tool installed:

```bash
sqlite3 messages.db
```

This opens the database and creates the file if it does not exist once you write data.

Inside SQLite, improve the output format:

```text
.headers on
.mode column
```

## Create a table

Store the reception time, topic, payload, and retained-message flag:

```sql
CREATE TABLE messages (
    id INTEGER PRIMARY KEY,
    received_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    topic TEXT NOT NULL,
    payload TEXT NOT NULL,
    retained INTEGER NOT NULL DEFAULT 0
        CHECK (retained IN (0, 1))
);
```

- `id` is assigned automatically when omitted.
- `received_at` defaults to the current **UTC** time.
- `payload` stores text, including JSON text.
- `retained` stores the received MQTT retain flag: `0` = false, `1` = true.

## Manage records: CRUD

**CRUD** means Create, Read, Update, and Delete.

### Create — insert messages

```sql
INSERT INTO messages (topic, payload, retained)
VALUES ('devices/lamp/status', 'ON', 0);

INSERT INTO messages (topic, payload, retained)
VALUES ('devices/sensor/temperature', '{"value":22.5}', 0);
```

### Read — find messages

```sql
-- All messages
SELECT * FROM messages;

-- Messages from one topic
SELECT received_at, payload
FROM messages
WHERE topic = 'devices/lamp/status';

-- Five most recently inserted messages
SELECT * FROM messages
ORDER BY id DESC
LIMIT 5;
```

### Update — change a record

```sql
UPDATE messages
SET payload = 'OFF'
WHERE id = 1;
```

### Delete — remove a record

```sql
DELETE FROM messages
WHERE id = 2;
```

**Remember:** without `WHERE`, `UPDATE` or `DELETE` affects **every row**. In a message history, applications normally insert new rows instead of changing old messages.

Exit SQLite:

```text
.quit
```

## Quick exercise

1. Create `messages.db` and the `messages` table.
2. Insert three lamp status messages: `ON`, `OFF`, `ON`.
3. Display only messages whose payload is `ON`.
4. Update one message using its ID.
5. Delete one message using its ID.
6. Close and reopen the database. Check that the remaining messages are still there.

---

# Introduction to Python

## Why use Python with SQLite?

SQL manages the data, while **Python connects the database to the rest of the application**.

Instead of manually entering every message, a Python program can:

- receive MQTT messages automatically;
- extract the topic and payload;
- store each message in SQLite;
- read and process stored messages.

In our system, this Python program is the **ingestor**: **MQTT broker → Python ingestor → SQLite database**

Python includes the `sqlite3` module, so no additional database package is needed.

## Connect and create a table

```python
import sqlite3

# Open the database file, or create it if needed.
connection = sqlite3.connect("messages.db")

connection.execute("""
    CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY,
        received_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        topic TEXT NOT NULL,
        payload TEXT NOT NULL,
        retained INTEGER NOT NULL DEFAULT 0
            CHECK (retained IN (0, 1))
    )
""")

connection.commit()
```

`IF NOT EXISTS` allows the program to run again without trying to recreate an existing table. The timestamp is assigned automatically in **UTC**.

**File location matters:** `messages.db` is relative to the directory where you run the program.

## Insert a message

```python
topic = "devices/lamp/status"
payload = "ON"
retained = 0

connection.execute(
    "INSERT INTO messages (topic, payload, retained) VALUES (?, ?, ?)",
    (topic, payload, retained)
)

connection.commit()
```

- `execute()` runs an SQL statement.
- `?` placeholders pass values safely. Avoid inserting values using f-strings.
- `commit()` saves the changes.

In an MQTT ingestor, this insertion happens **when a message arrives**.

## Read messages

```python
cursor = connection.execute("""
    SELECT id, received_at, topic, payload
    FROM messages
    ORDER BY id DESC
    LIMIT 5
""")

for message_id, received_at, topic, payload in cursor:
    print(f"{message_id}: {received_at} | {topic} | {payload}")
```

The returned **cursor** lets Python iterate through the result rows. Each row contains the columns selected by the query.

## Update and delete records

```python
# Update the message whose ID is 1.
connection.execute(
    "UPDATE messages SET payload = ? WHERE id = ?",
    ("OFF", 1)
)

# Delete the message whose ID is 2.
connection.execute(
    "DELETE FROM messages WHERE id = ?",
    (2,)
)

connection.commit()
```

`(2,)` is a one-item Python tuple. The comma is required.

**Remember:** without `WHERE`, an update or deletion affects every row. A message ingestor normally **adds new records**, preserving the message history.

## Close the connection

```python
connection.close()
```

**The basic workflow is**: Connect → Execute SQL → Commit changes → Close

## Quick exercise

Create `database_demo.py` using the examples above.

1. Insert three lamp status messages: `ON`, `OFF`, and `ON`.
2. Display the stored messages with their IDs and timestamps.
3. Add a query that displays only messages whose payload is `ON`.
4. Update one record using its ID.
5. Delete one record using its ID.
6. Run the program again and check what happens to the stored data.

Run the program:

```bash
python3 database_demo.py
```

---

# Introduction MQTT, Python, and databases

## Why connect MQTT to a database?

MQTT delivers messages, but it does not provide a queryable message history. **Storing messages in SQLite allows us to inspect past events**, calculate statistics, and display history on a web page.

A retained MQTT message provides the latest retained value for a topic—not its full history.

The Python **ingestor** connects these two systems: **Simulated lamp → MQTT broker → Python ingestor → SQLite**

The ingestor subscribes to the lamp’s status topic and saves each received message as a new database record.

## Install the MQTT library

SQLite support is included in Python. Install the MQTT client library in a virtual environment (optional):

```bash
python3 -m venv .venv
source .venv/bin/activate
python -m pip install paho-mqtt
```

## Create `ingestor.py`

Replace the broker address, username, password, and topic with your existing MQTT settings.

```python
import sqlite3
import paho.mqtt.client as mqtt

BROKER = "192.168.1.100"
USERNAME = "ingestor"
PASSWORD = "replace-with-your-password"
TOPIC = "devices/lamp/status"

# Prepare the database.
connection = sqlite3.connect("messages.db")

connection.execute("""
    CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY,
        received_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        topic TEXT NOT NULL,
        payload TEXT NOT NULL,
        retained INTEGER NOT NULL DEFAULT 0
            CHECK (retained IN (0, 1))
    )
""")
connection.commit()


# Runs when the broker responds to our connection request.
def on_connect(client, userdata, flags, reason_code, properties):
    if reason_code.is_failure:
        print(f"Connection failed: {reason_code}")
        return

    print("Connected to MQTT broker")
    client.subscribe(TOPIC)


# Runs whenever a subscribed message arrives.
def on_message(client, userdata, message):
    payload = message.payload.decode("utf-8", errors="replace")

    connection.execute(
        """
        INSERT INTO messages (topic, payload, retained)
        VALUES (?, ?, ?)
        """,
        (message.topic, payload, int(message.retain))
    )
    connection.commit()

    print(f"Saved: {message.topic} | {payload}")


client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
client.username_pw_set(USERNAME, PASSWORD)

client.on_connect = on_connect
client.on_message = on_message

try:
    client.connect(BROKER, 1883, 60)
    client.loop_forever()
except KeyboardInterrupt:
    print("\nStopping ingestor")
finally:
    client.disconnect()
    connection.close()
```

This example assumes the lamp publishes **text**, such as `ON`, `OFF`, or JSON.

## Run the ingestor

With the virtual environment activated:

```bash
python ingestor.py
```

Leave it running. Stop it with **Ctrl+C**.

| Part | Responsibility |
|---|---|
| `on_connect()` | Subscribes after a successful connection, including reconnections |
| `on_message()` | Receives a message and inserts it into SQLite |
| `commit()` | Saves the inserted record |
| `loop_forever()` | Processes MQTT network traffic and runs the callbacks |

The timestamp records **when the ingestor receives the message**, which may differ from when the device originally published it.

## Check the stored messages

In another terminal, open the database from the same directory:

```bash
sqlite3 messages.db
```

```sql
.headers on
.mode column

SELECT * FROM messages
ORDER BY id DESC
LIMIT 10;
```

Exit with `.quit`.

## Quick exercise

Continue using your existing controller, simulated lamp, and authenticated MQTT broker.

1. Configure and start the ingestor.
2. Send the lamp three commands: `ON`, `OFF`, and `ON`.
3. Confirm that the ingestor receives the lamp’s **status messages**.
4. Query SQLite and find the corresponding records.
5. Check the topic, payload, timestamp, and retained flag.
6. Stop the ingestor and confirm that the records remain in the database.

> [!NOTE]
> You can use the following commands in the Linux terminal.

```bash
mosquitto_pub \
  -h <IP address OR DNS name> \
  -u <username if needed> \
  -P '<password if needed>' \
  -t '<topic>' \
  -m 'ON'
```

```bash
mosquitto_pub \
  -h <IP address OR DNS name> \
  -u <username if needed> \
  -P '<password if needed>' \
  -t '<topic>' \
  -m 'OFF'
```

```bash
mosquitto_sub \
  -h <IP address OR DNS name> \
  -u <username if needed> \
  -P '<password if needed>' \
  -t '<topic>'
```

---

# Introduction to Web application

## Sharing stored information through the web

Our Python ingestor stores MQTT messages in SQLite. A **web application** makes those messages available to users in a browser.

The browser sends an **HTTP request** to a server. The server reads the database and sends an **HTTP response**.

There are two common approaches:

| Approach | Server returns | Who prepares the displayed page? |
|---|---|---|
| **With a data API** | Data, usually JSON | JavaScript running in the browser |
| **Without a separate data API** | A complete HTML page | The server |

In both approaches, **the server accesses SQLite**. The browser does not open the database file directly.

## With an API

An **API (Application Programming Interface)** defines how software can request information or actions from another application.

A web API provides **endpoints**: addresses for particular operations.

For example:

```http
GET /api/messages
```

The server queries SQLite and returns JSON:

```json
[
  {
    "id": 1,
    "topic": "devices/lamp/status",
    "payload": "ON"
  },
  {
    "id": 2,
    "topic": "devices/lamp/status",
    "payload": "OFF"
  }
]
```

JavaScript in the web page requests the data and displays it:

```html
<h1>Lamp messages</h1>
<pre id="messages"></pre>

<script>
async function loadMessages() {
  const response = await fetch("/api/messages");
  const messages = await response.json();

  document.querySelector("#messages").textContent =
    messages.map(message =>
      `${message.id}: ${message.topic} → ${message.payload}`
    ).join("\n");
}

loadMessages();
</script>
```

This example assumes the server already provides `/api/messages`.

**Why use an API?**

- The same data can serve a website, a mobile application, and other programs.
- The page can load fresh data without reloading the whole page.
- The data and its presentation can be developed separately.

## Without a separate API

For a small application, the server can read SQLite and build the HTML directly. For example, the browser requests:

```http
GET /messages
```

The server reads the stored messages and returns a complete page:

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lamp messages</title>
</head>
<body>
  <h1>Lamp messages</h1>
  <ul>
    <li>1: devices/lamp/status → ON</li>
    <li>2: devices/lamp/status → OFF</li>
  </ul>
</body>
</html>
```

The list items are **generated by the server from database records**, rather than written manually.

**Why use this approach?**

- It is simple for a small website.
- Browser JavaScript is optional.
- One server handles both database queries and page generation.

To see new messages, the user can refresh the page. When generating HTML, the server must escape message text so it appears as text safely.

## Example: the same information, two responses

| Browser request | Server action | Response |
|---|---|---|
| `GET /api/messages` | Read the latest messages | JSON containing message records |
| `GET /messages` | Read the latest messages and generate a page | HTML containing a message list |

**An API is useful when other software needs the data. A server-generated page is enough when users simply need to view it.**

---

# Use case: Using Node

Use **one Node.js application** to demonstrate both approaches with the existing `messages.db`.

| URL | Response |
|---|---|
| `/api/messages` | Messages as JSON |
| `/messages` | A complete HTML page |

## Prepare the application

Use **Node.js 24** for this example. It includes SQLite support through `node:sqlite`. [Node.js v24.22.0 Documentation](https://nodejs.org/docs/latest/api/sqlite.html)

In the directory containing `messages.db`, run:

```bash
npm init -y
npm install express
```

The database must already contain the `messages` table created in the Python exercise.

## Create `app.cjs`

Express handles HTTP requests. Here, `res.json()` returns data and `res.send()` returns HTML. [Express.js](https://expressjs.com/en/guide/routing//)

```javascript
const express = require("express");
const { DatabaseSync } = require("node:sqlite");
const path = require("node:path");

const app = express();

// Open the existing database beside this script.
const db = new DatabaseSync(
  path.join(__dirname, "messages.db"),
  { readOnly: true }
);

function getMessages() {
  return db.prepare(`
    SELECT id, received_at, topic, payload, retained
    FROM messages
    ORDER BY id DESC
    LIMIT 10
  `).all();
}

// Escape database values before inserting them into HTML.
function escapeHtml(value) {
  return String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

// CASE 1: API — return data as JSON.
app.get("/api/messages", (req, res) => {
  const messages = getMessages();
  res.json(messages);
});

// CASE 2: No separate data API — generate HTML here.
app.get("/messages", (req, res) => {
  const messages = getMessages();

  const rows = messages.map(message => `
    <tr>
      <td>${message.id}</td>
      <td>${escapeHtml(message.received_at)}</td>
      <td>${escapeHtml(message.topic)}</td>
      <td>${escapeHtml(message.payload)}</td>
      <td>${message.retained}</td>
    </tr>
  `).join("");

  res.send(`
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>MQTT messages</title>
    </head>
    <body>
      <h1>Latest MQTT messages</h1>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Received (UTC)</th>
            <th>Topic</th>
            <th>Payload</th>
            <th>Retained</th>
          </tr>
        </thead>
        <tbody>
          ${rows}
        </tbody>
      </table>
      <p>Refresh the page to see new messages.</p>
    </body>
    </html>
  `);
});

app.listen(3000, () => {
  console.log("Open http://localhost:3000/messages");
});
```

**Both routes run the same database query. The difference is how they return the result.**

## Run and compare

```bash
node app.cjs
```

Open these addresses on the computer running Node.js:

- [JSON API](http://localhost:3000/api/messages)
- [Server-generated page](http://localhost:3000/messages)

The API returns records for other software to use. The HTML route returns a page ready for the browser to display.

## Display API data on a web page

To demonstrate a browser using the API, add this route **before `app.listen()`**, then restart Node.js:

```javascript
app.get("/api-demo", (req, res) => {
  res.send(`
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>API example</title>
    </head>
    <body>
      <h1>Messages loaded through the API</h1>
      <button onclick="loadMessages()">Load messages</button>
      <pre id="output"></pre>

      <script>
        async function loadMessages() {
          const response = await fetch("/api/messages");
          const messages = await response.json();

          document.querySelector("#output").textContent =
            JSON.stringify(messages, null, 2);
        }

        loadMessages();
      </script>
    </body>
    </html>
  `);
});
```

Open [the API demonstration](http://localhost:3000/api-demo). The **Load messages** button updates the displayed data without reloading the whole page.

## Quick exercise

1. Run the Python ingestor and the Node.js application in separate terminals.
2. Open `/messages` and `/api-demo`.
3. Send an `ON` or `OFF` command to the lamp.
4. Refresh `/messages` to see the new status record.
5. On `/api-demo`, click **Load messages**.
6. Open the browser’s **Developer Tools → Network** panel and click the button again. Find the `/api/messages` request and inspect its JSON response.