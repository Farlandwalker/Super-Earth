let selectedEvents = []; // 存储已选择的活动

document.addEventListener("DOMContentLoaded", function() {
    // 获取导航栏中所有的链接
    const navLinks = document.querySelectorAll("nav a");
    
    navLinks.forEach(link => {
        // 如果链接不是登录页面，则添加 target="_blank"
        if (!link.href.includes("login.html")) {
            link.setAttribute("target", "_blank");
        }
    });
});

document.querySelectorAll(".open-new-window").forEach(function (link) {
    link.addEventListener("click", function (event) {
        event.preventDefault();
        window.open(link.href, "_blank");
    });
});


function editProfile() {
    document.getElementById("user-info").style.display = "none";
    document.getElementById("edit-form").style.display = "block";
    
    document.getElementById("email").value = document.getElementById("user-email").innerText;
}

function updateProfile() {
    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();

    // 验证姓名和邮箱
    if (name === "" || !validateEmail(email)) {
        alert("请输入有效的姓名和邮箱！");
        return;
    }

    document.getElementById("user-name").innerText = escapeHtml(name);
    document.getElementById("user-email").innerText = escapeHtml(email);

    document.getElementById("edit-form").style.display = "none";
    document.getElementById("user-info").style.display = "block";
}

// 验证邮箱格式
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// 转义 HTML 特殊字符
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function cancelEdit() {
    document.getElementById("edit-form").style.display = "none";
    document.getElementById("user-info").style.display = "block";
}

function closeDetails() {
    document.getElementById("charity-details").style.display = "none";
}

function signUp(eventName) {
    // 获取活动时间
    let timeSlot = document.querySelector(`#time-slot`).value;
    
    // 更新活动名称和时间到报名表中
    document.getElementById('event').value = `${eventName} - ${timeSlot}`;
}

function submitVolunteer() {
    const name = document.getElementById("volunteer-name").value;
    const email = document.getElementById("volunteer-email").value;

    if (selectedEvents.length > 0) {
        alert(`感谢你的报名，${name}！你已报名参加以下活动:\n${selectedEvents.map(event => `${event.name} - 日期: ${event.date} - 时间段: ${event.time}`).join('\n')}\n我们会通过 ${email} 联系你。`);
        
        // 重置表单
        document.getElementById("volunteerForm").reset();
        selectedEvents = []; // 清空已选择的活动
        document.getElementById("signup-form").style.display = "none";
    } else {
        alert("请至少选择一个活动进行报名。");
    }
}

function cancelSignUp() {
    document.getElementById("signup-form").style.display = "none";
}

function filterCharities() {
    const searchValue = document.getElementById("search-input").value.toLowerCase();
    
    // 进行输入消毒
    const sanitizedValue = escapeHtml(searchValue);
    
    const charityItems = document.querySelectorAll("#charity-events ul li");

    charityItems.forEach(item => {
        const title = item.querySelector("h4").innerText.toLowerCase();
        if (title.includes(sanitizedValue)) {
            item.style.display = ""; // 显示
        } else {
            item.style.display = "none"; // 隐藏
        }
    });
}

function base64Encode(input) {
    return btoa(input); // 使用 btoa() 函数进行 Base64 编码
}

function submitDonation() {
    const amount = parseFloat(document.getElementById("donation-amount").value);

    // 验证捐赠金额
    if (isNaN(amount) || amount <= 0) {
        alert("请输入有效的捐赠金额。");
        return;
    }

    const messageDiv = document.getElementById("donation-message");
    messageDiv.innerText = `感谢您的捐赠！您已成功捐赠 ${amount} 元。`;
    messageDiv.style.display = "block";

    // 重置捐赠表单
    document.getElementById("donationForm").reset();

}

function searchEvents() {
    const searchInput = document.getElementById("search-input").value.trim().toLowerCase();
    const eventsList = document.querySelectorAll("#events-list li");
    const eventResults = document.getElementById("event-results");

    eventResults.innerHTML = ""; // 清空上次搜索结果

    if (!searchInput) {
        eventResults.innerHTML = "<p>请输入要搜索的活动名称。</p>";
        return;
    }

    let found = false;

    eventsList.forEach(event => {
        const eventText = event.textContent.toLowerCase();
        if (eventText.includes(searchInput)) {
            const link = document.createElement("a");
            link.href = "#"; // 可根据需求设置跳转链接
            link.textContent = event.textContent;
            link.classList.add("search-result-item");

            const resultItem = document.createElement("p");
            resultItem.appendChild(link);
            eventResults.appendChild(resultItem);

            found = true;
        }
    });

    if (!found) {
        eventResults.innerHTML = "<p>未找到相关的活动。</p>";
    }
}

// 显示二维码的函数
function showQRCode() {
    // 获取捐赠金额
    var amount = document.getElementById('amount').value;

    // 判断是否填写金额
    if (amount && amount > 0) {
        // 显示二维码容器
        document.getElementById('qrCodeContainer').style.display = 'block';

        // 更新二维码的内容，根据捐赠金额生成二维码（假设你有相应的生成二维码功能）
        // 这里你可以替换为动态生成的二维码图片URL
        var qrCodeUrl = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=Donate%20" + amount + "%20USD"; 
        document.getElementById('qrCode').src = qrCodeUrl;
    } else {
        alert("Please enter a valid donation amount.");
    }
}
