if (document.getElementById('go')) {
    document.getElementById('go').addEventListener("click", () => {
        fetch('/login', {
            method: 'POST',
            headers: {
                'Accept': 'text/plain;charset=utf-8',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                login: document.getElementById('login').value,
                password: document.getElementById('password').value,
            }),
            cache: "no-cache",
        })
            .then(response => response.json())
            .then(response => {
                if ((response.code === 0)) {
                    location.reload();
                } else {
                    document.getElementById('login-error').innerHTML = response.message;
                }
            })
            .catch((e) => {
                console.log(e);
            });
    }, false);
}

if (document.getElementById('logout')) {
    document.getElementById('logout').addEventListener("click", () => {
        fetch('/logout', {
            method: 'GET',
            headers: {
                'Content-Type': 'text/plain;charset=utf-8',
            },
            cache: "no-cache",
        })
            .then(response => response.text())
            .then(response => {
                window.location.replace("/");
            })
            .catch((e) => {
                console.log(e);
            });
    }, false);
}

if (document.getElementById('register')) {
    document.getElementById('register').addEventListener("click", () => {
        fetch('/register', {
            method: 'POST',
            headers: {
                'Accept': 'text/plain;charset=utf-8',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                login: document.getElementById('login').value,
                password: document.getElementById('password').value,
            }),
            cache: "no-cache",
        })
            .then(response => response.json())
            .then(response => {
                if ((response.code === 0)) {
                    document.getElementById('login').value = '';
                    document.getElementById('password').value = '';
                }
                document.getElementById("register-error").innerHTML = response.message;
            })
            .catch((e) => {
                console.log(e);
            });
    }, false);
}