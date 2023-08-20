Sure, I can help you with that.

Here is the README.md file for the code you provided:

## ERs Register Page

This is the register page for the Exam Registration System. It allows users to create a new account.

### Instructions

To use this page, simply enter your username, email address, password, and confirm password. Then click the "Sign-Up" button.

### Code Snippets

Here are some code snippets from the register page:

```html
<form action="" method="post">
  <div class="text-input">
    <i class="fa-solid fa-user"></i>
    <input type="text" name="username" placeholder="UserName" />
  </div>
  <div class="text-input">
    <i class="fa-solid fa-at"></i>
    <input type="email" name="email" placeholder="E-mail" />
  </div>
  <div class="text-input">
    <i class="fa-solid fa-lock"></i>
    <input type="password" name="password" placeholder="Password" />
  </div>
  <div class="text-input">
    <i class="fa-solid fa-lock"></i>
    <input type="password" name="cpassword" placeholder="Password" />
  </div>
  <input type="submit" name="reg-btn" value="Sign-Up" />
</form>
```

```css
.text-input {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.text-input i {
  margin-right: 1rem;
}

.btn {
  background-color: var(--primary);
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  cursor: pointer;
}
```

### Explanation

The register page is a simple form that allows users to create a new account. The form asks for the user's username, email address, password, and confirm password. Once the user has entered all of their information, they can click the "Sign-Up" button to create their