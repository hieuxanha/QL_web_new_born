let currentIndex = 0;
const slides = document.querySelectorAll(".slides img");
const dotsContainer = document.getElementById("dots-container");

// Tạo các dot tự động dựa trên số lượng ảnh
function createDots() {
  for (let i = 0; i < slides.length; i++) {
    const dot = document.createElement("span");
    dot.classList.add("dot");
    dot.addEventListener("click", () => goToSlide(i));
    dotsContainer.appendChild(dot);
  }
}

// Chuyển đến slide tiếp theo
function nextSlide() {
  currentIndex = (currentIndex + 1) % slides.length;
  updateSlider();
}

// Chuyển về slide trước
function prevSlide() {
  currentIndex = (currentIndex - 1 + slides.length) % slides.length;
  updateSlider();
}

// Cập nhật slide và dot active
function updateSlider() {
  const offset = -currentIndex * 100;
  document.querySelector(".slides").style.transform = `translateX(${offset}%)`;

  const dots = document.querySelectorAll(".dot");
  dots.forEach((dot, index) => {
    if (index === currentIndex) {
      dot.classList.add("active");
    } else {
      dot.classList.remove("active");
    }
  });
}

// Đi đến slide cụ thể khi click vào dot
function goToSlide(index) {
  currentIndex = index;
  updateSlider();
}

// Tự động chuyển slide sau mỗi 4 giây
function autoSlide() {
  setInterval(() => {
    nextSlide();
  }, 4000); // chuyển slide mỗi 4s
}

// Khởi tạo slide
createDots();
autoSlide();
