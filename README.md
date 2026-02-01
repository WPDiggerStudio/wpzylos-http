# WPZylos HTTP

[![PHP Version](https://img.shields.io/badge/php-%5E8.0-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![GitHub](https://img.shields.io/badge/GitHub-WPDiggerStudio-181717?logo=github)](https://github.com/WPDiggerStudio/wpzylos-http)

HTTP Request, Response and Middleware pipeline for WPZylos framework.

📖 **[Full Documentation](https://wpzylos.com)** | 🐛 **[Report Issues](https://github.com/WPDiggerStudio/wpzylos-http/issues)**

---

## ✨ Features

- **Request Object** — Object-oriented request handling
- **Response Object** — JSON, HTML, Redirect responses
- **FormRequest** — Request validation with rules
- **Middleware** — Request/response middleware pipeline
- **Input Handling** — Sanitized input access

---

## 📋 Requirements

| Requirement | Version |
| ----------- | ------- |
| PHP         | ^8.0    |
| WordPress   | 6.0+    |

---

## 🚀 Installation

```bash
composer require wpdiggerstudio/wpzylos-http
```

---

## 📖 Quick Start

```php
use WPZylos\Framework\Http\Request;
use WPZylos\Framework\Http\Response;

// Get request data
$request = Request::capture();
$name = $request->input('name');
$id = $request->query('id');

// Send response
return Response::json(['success' => true]);
return Response::html($view);
return Response::redirect('/dashboard');
```

---

## 🏗️ Core Features

### Request Object

```php
$request = Request::capture();

// Input data (POST)
$email = $request->input('email');
$all = $request->all();

// Query parameters (GET)
$page = $request->query('page', 1);

// Check existence
if ($request->has('token')) {
    // ...
}

// Files
$file = $request->file('avatar');
```

### Response Types

```php
// JSON response
return Response::json(['data' => $data]);
return Response::json(['error' => 'Not found'], 404);

// HTML response
return Response::html('<h1>Hello</h1>');

// Redirect
return Response::redirect('/dashboard');
return Response::redirect(admin_url());
```

### FormRequest Validation

```php
class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'name' => 'required|min:2',
            'password' => 'required|min:8',
        ];
    }
}
```

---

## 📦 Related Packages

| Package                                                                    | Description            |
| -------------------------------------------------------------------------- | ---------------------- |
| [wpzylos-core](https://github.com/WPDiggerStudio/wpzylos-core)             | Application foundation |
| [wpzylos-routing](https://github.com/WPDiggerStudio/wpzylos-routing)       | URL routing            |
| [wpzylos-validation](https://github.com/WPDiggerStudio/wpzylos-validation) | Validation rules       |
| [wpzylos-scaffold](https://github.com/WPDiggerStudio/wpzylos-scaffold)     | Plugin template        |

---

## 📖 Documentation

For comprehensive documentation, tutorials, and API reference, visit **[wpzylos.com](https://wpzylos.com)**.

---

## ☕ Support the Project

If you find this package helpful, consider buying me a coffee! Your support helps maintain and improve the WPZylos ecosystem.

<a href="https://www.paypal.com/donate/?hosted_button_id=66U4L3HG4TLCC" target="_blank">
  <img src="https://img.shields.io/badge/Donate-PayPal-blue.svg?style=for-the-badge&logo=paypal" alt="Donate with PayPal" />
</a>

---

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.

---

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

**Made with ❤️ by [WPDiggerStudio](https://github.com/WPDiggerStudio)**
