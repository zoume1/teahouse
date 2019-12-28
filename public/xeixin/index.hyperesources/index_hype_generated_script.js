//	HYPE.documents["index"]

(function () {
    (function m() {
        function k(a, b, c, d) {
            var e = !1;
            null == window[a] && (null == window[b] ? (window[b] = [], window[b].push(m), a = document.getElementsByTagName("head")[0], b = document.createElement("script"), e = l, false == !0 && (e = ""), b.type = "text/javascript", "" != d && (b.integrity = d, b.setAttribute("crossorigin", "anonymous")), b.src = e + "/" + c, a.appendChild(b)) : window[b].push(m), e = !0);
            return e
        }
        var l = "index.hyperesources", f = "index", g = "index_hype_container";
        if (false == !1) try {
            for (var c = document.getElementsByTagName("script"), a = 0; a < c.length; a++) {
                var d = c[a].src, b = null != d ? d.indexOf("/index_hype_generated_script.js") : -1; if (-1 != b) {
                    l = d.substr(0, b);
                    break
                }
            }
        } catch (p) { }
        c = navigator.userAgent.match(/MSIE (\d+\.\d+)/);
        c = parseFloat(c && c[1]) || null;
        d = !0 == (null != window.HYPE_654F || null != window.HYPE_dtl_654F) || false == !0 || null != c && 10 > c;
        a = !0 == d ? "HYPE-654.full.min.js" : "HYPE-654.thin.min.js";
        c = !0 == d ? "F" : "T"; d = d ? "" : "";
        if (false == !1 && (a = k("HYPE_654" + c, "HYPE_dtl_654" + c, a, d),
            false == !0 && (a = a || k("HYPE_w_654", "HYPE_wdtl_654", "HYPE-654.waypoints.min.js", "")),
            false == !0 && (a = a || k("Matter", "HYPE_pdtl_654", "HYPE-654.physics.min.js", "")), a)) return;
        d = window.HYPE.documents; if (null != d[f]) {
            b = 1; a = f; do f = "" + a + "-" + b++; while (null != d[f]);
            for (var e = document.getElementsByTagName("div"), b = !1, a = 0; a < e.length; a++)
                if (e[a].id == g && null == e[a].getAttribute("HYP_dn")) {
                    var b = 1, h = g;
                    do g = "" + h + "-" + b++;
                    while (null != document.getElementById(g));
                    e[a].id = g; b = !0; break
                } if (!1 == b) return
        }
        b = []; b = []; e = {}; h = {};
        for (a = 0; a < b.length; a++)try {
            h[b[a].identifier] = b[a].name, e[b[a].name] = eval("(function(){return " + b[a].source + "})();")
        } catch (n) {
            window.console && window.console.log(n), e[b[a].name] = function () { }
        }
        c = new window["HYPE_654" + c](f, g, {
            "3": { p: 1, n: "PastedVector-3.svg", g: "25", t: "image/svg+xml" },
            "-2": { n: "blank.gif" },
            "4": {
                p: 1, n: "PastedVector-4.svg",
                g: "27", t: "image/svg+xml"
            },
            "0": {
                p: 1, n: "PastedVector.svg",
                g: "10", t: "image/svg+xml"
            },
            "5": { p: 1, n: "pai.png", g: "39", t: "@1x" },
            "1": { p: 1, n: "PastedVector-1.svg", g: "21", t: "image/svg+xml" },
            "6": { p: 1, n: "PastedVector-5.svg", g: "67", t: "image/svg+xml" },
            "2": { p: 1, n: "PastedVector-2.svg", g: "23", t: "image/svg+xml" },
            "-1": { n: "PIE.htc" }
        },
            l, [], e,
            [{ n: "\u5f00\u95e8\u9875", o: "5", X: [0] },
            { n: "\u9886\u53d6\u9875", o: "41", X: [1] }],
            [{
                o: "9", p: "600px",
                cA: false, Y: 320, Z: 568,
                L: [], c: "#D6D6D6", bY: 1,
                d: 320, U: {},
                T: {
                    kTimelineDefaultIdentifier: {
                        q: false, z: 1.12, i: "kTimelineDefaultIdentifier",
                        n: "Main Timeline",
                        a: [{ f: "c", y: 0, z: 0.14, i: "e", e: 1, s: 0, o: "83" },
                        { f: "c", y: 0.08, z: 0.14, i: "e", e: 1, s: 0, o: "77" },
                        { f: "c", y: 0.08, z: 0.14, i: "e", e: 1, s: 0, o: "82" },
                        { f: "c", y: 0.11, z: 0.14, i: "e", e: 1, s: 0, o: "79" },
                        { f: "c", y: 0.14, z: 0.14, i: "e", e: 1, s: 0, o: "78" },
                        { f: "c", y: 0.14, z: 0.14, i: "e", e: 1, s: 0, o: "80" },
                        { f: "c", y: 0.14, z: 0.14, i: "e", e: 1, s: 0, o: "84" },
                        { f: "c", y: 0.14, z: 0.14, i: "e", e: 1, s: 0, o: "76" },
                        { y: 0.14, i: "e", s: 1, z: 0, o: "83", f: "c" },
                        { f: "c", y: 0.22, z: 0.14, i: "e", e: 1, s: 0, o: "85" },
                        { y: 0.22, i: "e", s: 1, z: 0, o: "77", f: "c" },
                        { y: 0.22, i: "e", s: 1, z: 0, o: "82", f: "c" },
                        { y: 0.25, i: "e", s: 1, z: 0, o: "79", f: "c" },
                        { f: "c", y: 0.28, z: 0.14, i: "e", e: 1, s: 0, o: "81" },
                        { y: 0.28, i: "e", s: 1, z: 0, o: "78", f: "c" },
                        { y: 0.28, i: "e", s: 1, z: 0, o: "80", f: "c" },
                        { y: 0.28, i: "e", s: 1, z: 0, o: "84", f: "c" },
                        { y: 0.28, i: "e", s: 1, z: 0, o: "76", f: "c" },
                        { y: 1.06, i: "e", s: 1, z: 0, o: "85", f: "c" },
                        { y: 1.12, i: "e", s: 1, z: 0, o: "81", f: "c" }],
                        f: 30, b: []
                    }
                },
                bZ: 180,
                O: ["83", "79", "78", "76", "80", "85", "82", "77", "84", "81"],
                n: "iPhone", "_": 0,
                v: {
                    "78": {
                        h: "10", p: "no-repeat", x: "visible", a: 204, dB: "img", q: "100% 100%", j: "absolute", r: "inline", z: 3, b: 131, k: "div", d: 82, c: 82, e: 0
                    },
                    "81": {
                        b: 390, z: 11, K: "Solid", c: 127, L: "Solid", d: 51, aS: 6, M: 1, e: 0, bD: "none",
                        N: 1, aT: 6, dB: "button", O: 1, g: "#F0F0F0", aU: 6, P: 1, Q: 3, aV: 6, R: "#808080",
                        j: "absolute", S: 0, k: "div", aI: 6, T: 0, l: 0, aJ: 6, m: "#D8D8D8",
                        n: "#FFF", aK: 6, aL: 6, A: "#A0A0A0", B: "#A0A0A0", Z: "break-word",
                        r: "inline", C: "#A0A0A0", D: "#A0A0A0", t: 38, F: "center",
                        aA: { a: [{ d: 1.1, p: 1, g: 6, f: 1 }] },
                        G: "#A9A9A9", aP: "pointer", w: "\u542f\u5c01<br>", x: "visible",
                        I: "Solid", a: 89, y: "preserve", J: "Solid"
                    },
                    "76": {
                        h: "25", p: "no-repeat", x: "visible", a: 215, dB: "img", q: "100% 100%", j: "absolute",
                        r: "inline", z: 5, b: 140, k: "div", d: 65, c: 56, e: 0
                    },
                    "84": {
                        h: "27", p: "no-repeat", x: "visible", a: 215, dB: "img", q: "100% 100%", j: "absolute",
                        r: "inline", z: 8, b: 233, k: "div", d: 49, c: 66, e: 0
                    },
                    "79": {
                        h: "10", p: "no-repeat", x: "visible", a: 34, dB: "img", q: "100% 100%", j:
                            "absolute", r: "inline", z: 1, b: 131, k: "div", d: 82, c: 82, e: 0
                    },
                    "82": {
                        h: "10", p: "no-repeat", x: "visible", a: 204, dB: "img", q: "100% 100%",
                        j: "absolute", r: "inline", z: 4, b: 216, k: "div", d: 82, c: 82, e: 0
                    },
                    "77": {
                        h: "23", p: "no-repeat", x: "visible", a: 43, dB: "img", q: "100% 100%",
                        j: "absolute", r: "inline", z: 7, b: 230, k: "div", d: 55, c: 57, e: 0
                    },
                    "85": {
                        h: "10", p: "no-repeat", x: "visible", a: 34, dB: "img", q: "100% 100%", j: "absolute",
                        r: "inline", z: 2, b: 216, k: "div", d: 82, c: 82, e: 0
                    },
                    "80": {
                        h: "21", p: "no-repeat", x: "visible", a: 48, dB: "img", q: "100% 100%",
                        j: "absolute", r: "inline", z: 6, b: 148, k: "div", d: 54, c: 43, e: 0
                    },
                    "83": {
                        c: 166, V: "1", d: 206, I: "None", r: "inline", e: 0, J: "None",
                        bL: 0, W: "www.zhemu.com/fy-photo/SVG/jxw-1.svg", K: "None", gg: "1", L: "None", j: "absolute",
                        x: "visible", k: "div", Q: 7, z: 9, R: "#000", S: -1, a: 77, T: 3, b: 110
                    }
                }
            },
            {
                o: "53", p: "600px", cA: false, Y: 320, Z: 568, L: [],
                c: "#D6D6D6", bY: 1, d: 320, U: {},
                T: {
                    kTimelineDefaultIdentifier: {
                        q: false, z: 2.14, i: "kTimelineDefaultIdentifier", n: "Main Timeline",
                        a: [{ f: "c", y: 0, z: 0, i: "a", e: 104, s: 104, o: "92" },
                        { f: "f", y: 0, z: 0.29, i: "b", e: 41, s: 463, o: "92" },
                        { y: 0, i: "a", s: 104, z: 0, o: "92", f: "c" },
                        { f: "c", y: 0.25, z: 0.12, i: "e", e: 1, s: 0, o: "89" },
                        { f: "c", y: 0.25, z: 0.14, i: "e", e: 1, s: 0, o: "88" },
                        { f: "c", y: 0.25, z: 0.12, i: "a", e: 53, s: 129, o: "89" },
                        { y: 0.29, i: "b", s: 41, z: 0, o: "92", f: "c" },
                        { f: "c", y: 1, z: 0.19, i: "e", e: 1, s: 0, o: "91" },
                        { f: "c", y: 1.04, z: 0.13, i: "a", e: 177, s: 28, o: "86" },
                        { f: "c", y: 1.05, z: 0.12, i: "e", e: 1, s: 0, o: "86" },
                        { y: 1.07, i: "a", s: 53, z: 0, o: "89", f: "c" },
                        { y: 1.07, i: "e", s: 1, z: 0, o: "89", f: "c" },
                        { y: 1.09, i: "e", s: 1, z: 0, o: "88", f: "c" },
                        { f: "c", y: 1.12, z: 0.18, i: "t", e: 25, s: 12, o: "90" },
                        { f: "c", y: 1.12, z: 0.1, i: "e", e: 1, s: 0, o: "90" },
                        { y: 1.17, i: "a", s: 177, z: 0, o: "86", f: "c" },
                        { y: 1.17, i: "e", s: 1, z: 0, o: "86", f: "c" },
                        { y: 1.19, i: "e", s: 1, z: 0, o: "91", f: "c" },
                        { y: 1.22, i: "e", s: 1, z: 0, o: "90", f: "c" },
                        { y: 2, i: "t", s: 25, z: 0, o: "90", f: "c" },
                        { f: "c", y: 2, z: 0.14, i: "e", e: 1, s: 0, o: "87" },
                        { y: 2.14, i: "e", s: 1, z: 0, o: "87", f: "c" }],
                        f: 30, b: []
                    }
                },
                bZ: 180, O: ["86", "89", "88", "90", "87", "91", "92"],
                n: "iPhone", "_": 1,
                v: {
                    "91": {
                        c: 130, V: "1", d: 132, I: "None", r: "inline", e: 0, J: "None",
                        bL: 0, W: "www.zhemu.com/fy-photo/SVG/cc-ym.svg", K: "None", gg: "1", L: "None", j: "absolute", x: "visible", k: "div", Q: 7, z: 7, R: "#000", S: -1, a: 96, T: 3, b: 436
                    },
                    "86": {
                        h: "39", p: "no-repeat", x: "visible", aW: 0.2747231, q: "100% 100%", a: 28, j: "absolute", r: "inline", b: 65,
                        z: 1, dB: "img", d: 117, k: "div", c: 90, bL: 0.94442247, e: 0
                    }, "89": {
                        h: "39", p: "no-repeat", x: "visible", aW: 0.2747231,
                        q: "100% 100%", a: 129, j: "absolute", r: "inline", b: 65, z: 2, dB: "img", d: 117, k: "div", c: 90, bL: 0.94442247, e: 0
                    },
                    "92": {
                        h: "39", p: "no-repeat", x: "visible", aW: 0.2747231, dB: "img", q: "100% 100%", a: 104, r: "inline", j: "absolute",
                        z: 3, b: 463, d: 142, k: "div", c: 110
                    },
                    "87": {
                        b: 395, z: 8, K: "Solid", c: 101, L: "Solid", d: 27, aS: 6, M: 1, e: 0, bD: "none",
                        N: 1, aT: 6, dB: "button", O: 1, g: "#F0F0F0", aU: 6, P: 1, Q: 3, aV: 6, R: "#808080", j: "absolute", S: 0, k: "div", aI: 6, T: 0, l: 0, aJ: 6,
                        m: "#D8D8D8", n: "#FFF", aK: 6, aL: 6, A: "#A0A0A0", B: "#A0A0A0", Z: "break-word", r: "inline", C: "#A0A0A0", D: "#A0A0A0", t: 24, F: "center",
                        aA: { a: [{ d: 1.1, p: 1, g: 6, f: 2 }] },
                        G: "#A9A9A9", aP: "pointer", w: "\u9886\u53d6\u5c01\u8336<br>", x: "visible", I: "Solid", a: 107, y: "preserve", J: "Solid"
                    },
                    "90": {
                        aU: 8, G: "#FFF", c: 172, aV: 8, r: "inline", d: 151, e: 0, s: "Helvetica,Arial,Sans-Serif", t: 12, Z: "break-word",
                        w: "\u4e3b\u4eba\uff0c\u4e3b\u4eba\u60a8\u7684\u5c01\u8336\u5df2\u51c6\u5907\u5b8c\u6bd5,\u9700\u8981\u73b0\u5728\u5f00\u542f\u5417\uff1f",
                        j: "absolute", x: "visible", k: "div", y: "preserve", z: 5, aS: 8, aT: 8, a: 61, b: 237
                    },
                    "88": { h: "67", p: "no-repeat", x: "visible", a: 25, dB: "img", q: "100% 100%", j: "absolute", r: "inline", z: 4, b: 229, k: "div", d: 191, c: 269, e: 0 }
                }
            }], {}, h, {
            f: {
                p: 0, q:
                    [[0, 0, 0.1971, 0, 0.3391, 0.8944, 0.3636, 1],
                    [0.3636, 1, 0.3636, 1, 0.4425, 0.75, 0.5455, 0.75],
                    [0.5455, 0.75, 0.6519, 0.75, 0.7273, 1, 0.7273, 1],
                    [0.7273, 1, 0.7273, 1, 0.7718, 0.9375, 0.8182, 0.9375],
                    [0.8182, 0.9375, 0.8646, 0.9375, 0.9091, 1, 0.9091, 1],
                    [0.9091, 1, 0.9091, 1, 0.9294, 0.9844, 0.9546, 0.9844],
                    [0.9546, 0.9844, 0.9798, 0.9844, 1, 1, 1, 1]]
            }
        },
            null, false, true, -1, true, true, false, true, true);
        d[f] = c.API; document.getElementById(g).setAttribute("HYP_dn", f); c.z_o(this.body)
    })();
})();
