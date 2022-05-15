document.addEventListener("DOMContentLoaded", function () {

    const
        margin = 20,
        rootName = "Дерево объектов",
        defaultName = "Новый элемент",
        edit = document.getElementById("editor").dataset.edit.toString() === 'yes';

    // запрос
    const request = (url, method, body, success, error = null) => {
        fetch(url, {
            method: method,
            headers: {
                "Accept": "text/plain;charset=utf-8",
                "Content-Type": "application/json",
            },
            body: body ? JSON.stringify(body) : null,
            cache: "no-cache",
        })
            .then(response => response.json())
            .then(response => {
                if (response.code) {
                    if (error) {
                        error(response);
                    } else {
                        console.log(response);
                    }
                    return;
                }
                success(response);
            })
            .catch((e) => {
                console.log(e);
            });
    };

    // добавить элемент дерева
    const appendLeaf = (parent, id, title, level, root = false, empty = false) => {
        // создаем элемент
        const leaf = document.createElement("div");
        // сохраняем ID, позже понадобится
        leaf.dataset.id = id;
        leaf.classList.add("leaf");

        // постоянно делать отступ не получится, ограничимся 9м уровнем вложенности
        leaf.style.marginLeft = (level < 9 ? margin : 0) + "px";
        // элементы с уровнем вложенности 0 раскрыты, остальные по умолчанию закрыты
        if (level === 0) {
            leaf.classList.add("unfold");
        }

        // заголовок
        const header = document.createElement("div");
        header.classList.add("header");
        header.classList.add("clearfix");
        leaf.append(header);

        // контейнер для потомков
        const container = document.createElement("div");
        container.classList.add("container");
        leaf.append(container);

        // раскрыть
        if (!empty) {
            const unfold = document.createElement("div");
            unfold.classList.add("open");
            unfold.classList.add("button");
            unfold.classList.add("float-left");
            unfold.innerHTML = root ? "-" : "+";
            header.append(unfold);
            unfold.addEventListener("click", function (event) {
                const
                    header = event.target.parentElement,
                    currentLeaf = header.parentElement;
                if (currentLeaf.classList.contains("unfold")) {
                    currentLeaf.classList.remove("unfold");
                    event.target.innerHTML = "+";
                } else {
                    currentLeaf.classList.add("unfold");
                    event.target.innerHTML = "-";
                }
            });
        }

        // заголовок
        const name = document.createElement("div");
        name.classList.add("name");
        name.classList.add("float-left");
        name.innerHTML = title;
        name.id = "name-" + id;
        header.append(name);

        // Редактировать название и контент элемента
        name.addEventListener("click", function (event) {
            const
                header = event.target.parentElement,
                currentLeaf = header.parentElement;
            request("/tree/" + currentLeaf.dataset.id, "GET", null, (response) => {
                document.getElementById("editor").style.visibility = "visible";
                if (edit) {
                    document.getElementById("name").value = response.leaf.name;
                    document.getElementById("content").value = response.leaf.content;
                    document.getElementById("save").dataset.id = response.leaf.id;
                    document.getElementById("editor-error").innerHTML = "";
                } else {
                    document.getElementById("name").innerHTML = response.leaf.name;
                    document.getElementById("content").innerHTML = response.leaf.content;
                }
                const activeLeafs = document.getElementById("tree").querySelectorAll(".active");
                activeLeafs.forEach((element) => {
                    element.classList.remove("active");
                });
                header.classList.add("active");
            });
        });

        // удалить
        if (!root && edit) {
            const remove = document.createElement("div");
            remove.classList.add("button");
            remove.classList.add("remove");
            remove.classList.add("float-left");
            remove.innerHTML = "Удалить";
            header.append(remove);
            remove.addEventListener("click", function (event) {
                const
                    header = event.target.parentElement,
                    currentLeaf = header.parentElement;
                request("/delete", "POST", {id: currentLeaf.dataset.id}, () => {
                    currentLeaf.remove();
                    document.getElementById("editor").style.visibility = "hidden";
                });
            });
        }

        // добавить
        if (edit) {
            const add = document.createElement("div");
            add.classList.add("add");
            add.classList.add("button");
            add.innerHTML = "Добавить";
            leaf.append(add);
            add.addEventListener("click", function (event) {
                const
                    currentLeaf = event.target.parentElement;
                request(
                    "/save",
                    "POST",
                    {parent_id: currentLeaf.dataset.id, name: defaultName, content: ""},
                    (response) => {
                        document.getElementById("editor").style.visibility = "visible";
                        document.getElementById("name").value = defaultName;
                        document.getElementById("content").value = '';
                        document.getElementById("save").dataset.id = response.id;
                        const activeLeafs = document.getElementById("tree").querySelectorAll(".active");
                        activeLeafs.forEach((element) => {
                            element.classList.remove("active");
                        });
                        const leaf = appendLeaf(currentLeaf, response.id, defaultName, level + 1);
                        leaf.querySelector(".header").classList.add("active");
                    }
                );
            });

            // добавляем вспомогательные эффекты если уровень вложенности меньше 8
            if (level < 8) {
                add.style.marginLeft = margin + "px";
                container.classList.add("bordered");
                add.addEventListener("mouseover", function (event) {
                    const
                        currentLeaf = event.target.parentElement;
                    currentLeaf.querySelector(".container").classList.add("ready-to-add");
                });
                add.addEventListener("mouseleave", function (event) {
                    const
                        currentLeaf = event.target.parentElement;
                    currentLeaf.querySelector(".container").classList.remove("ready-to-add");
                });
            }
        }

        // добавляем созданный элемент к родительскому контейнеру
        if (root) {
            parent.append(leaf);
        } else {
            parent.querySelector(".container").append(leaf);
        }

        return leaf;
    };

    // рекурсивная функция для построения дерева
    const build = (tree, parent, level = 1) => {
        if (!tree.branches) return;
        tree.branches.forEach((leaf) => {
            // присоединяем элемент
            const el = appendLeaf(parent, leaf.id, leaf.name, level, false, leaf.branches.length === 0 && !edit);

            // Рекурсивно вызываем каждую ветвь
            if (leaf.branches.length > 0) {
                build(leaf, el, level + 1);
            }
        });
    };

    // сохранить новое название и контент элемента
    if (document.getElementById("save")) {
        document.getElementById("save").addEventListener("click", function (event) {
            request(
                "/save",
                "POST",
                {
                    id: event.target.dataset.id,
                    name: document.getElementById("name").value,
                    content: document.getElementById("content").value,
                },
                () => {
                    document.getElementById("editor").style.visibility = 'hidden';
                    const activeLeafs = document.getElementById("tree").querySelectorAll(".active");
                    activeLeafs.forEach((element) => {
                        element.classList.remove("active");
                    });
                    request('/tree/' + event.target.dataset.id, 'GET', null, (response) => {
                        document.getElementById("name-" + event.target.dataset.id).innerHTML = response.leaf.visible_name;
                    });
                },
                (response) => {
                    document.getElementById("editor-error").innerHTML = response.message;
                }
            );
        });
    }

    // добавляем корневой объект
    const root = appendLeaf(document.getElementById("tree"), 0, rootName, 0, true);

    // загружаем дерево
    request("/tree", "GET", null, (response) => {
        build(response.tree, root);
    });
});