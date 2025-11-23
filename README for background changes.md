# Home Page Background Change Guide

This guide explains how to change the background image on the home page of your website.

## Easy Method (Recommended)

1. Navigate to the `images/` folder in your project
2. Replace the file `homepage-bg.jpg` with your desired background image
3. Make sure your new image has the same filename: `homepage-bg.jpg`
4. The website will automatically use the new background image

**Available background images in your images folder:**
- `homepage-bg.jpg` (current)
- `homepage-bg2.jpg` (alternative)
- `retro-background.jpg`
- `brick-wall.jpg`
- `blog-background.jpg`
- `visa-bg.jpg`
- `accommodation-bg.jpg`

## Advanced Method (CSS Modification)

If you want more control or to use a different image without replacing files:

1. Open `index.css` in your code editor
2. Find the `body.home-page` CSS rule (around line 8)
3. Modify the `background` property to point to your desired image:

```css
body.home-page {
  /* ... other styles ... */
  background: url("images/your-new-image.jpg") no-repeat center center fixed;
  background-size: cover;
}
```

**Current CSS setting:**
```css
background: url("images/homepage-bg.jpg") no-repeat center center fixed;
background-size: cover;
```

## Tips

- Background images should be high quality and optimized for web use
- Recommended image dimensions: 1920x1080 or larger for best results
- The background is set to `fixed` positioning, so it stays in place when scrolling
- `background-size: cover` ensures the image covers the entire viewport

## Troubleshooting

- If the new background doesn't appear, check that the image file exists in the `images/` folder
- Clear your browser cache or do a hard refresh (Ctrl+F5)
- Make sure the image filename matches exactly in the CSS or file replacement
