module.exports = {
    content: ["../**/*.php","../navbar.php", "../**/*.html", "../**/*.js"],
    theme: {
        extend: {
            animation: {
'typewriter': 'typewriter 2s steps(11) forwards',
                'blink': 'blink 1s steps(11) infinite',
                'fade-in': 'fadeIn 1s ease-out forwards',
                'float': 'float 6s ease-in-out infinite'
            },
            keyframes: {
                typewriter: {
                    from: { width: '0' },
                    to: { width: '11ch' }
                },
                blink: {
                    from: { 'border-right-color': 'transparent' },
                    to: { 'border-right-color': 'rgb(255, 255, 153)' }
                },
                fadeIn: {
                    from: { opacity: '0' },
                    to: { opacity: '1' }
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-15px)' }
                }
            },
        }
    }
    plugins: [],
}
