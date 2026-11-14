import math
import random
from PIL import Image, ImageDraw

def create_cyber_matrix():
    width, height = 480, 200
    frames = []
    n_frames = 16
    
    random.seed(42)
    stars = [(random.randint(0, width), random.randint(0, int(height * 0.6)), random.choice([1, 1, 2])) for _ in range(45)]
    
    for f in range(n_frames):
        im = Image.new('RGB', (width, height), (12, 4, 28))
        draw = ImageDraw.Draw(im)
        
        # 1. Sky Gradient (Deep purple to dark magenta)
        for y in range(int(height * 0.65)):
            r = int(12 + (45 - 12) * (y / (height * 0.65)))
            g = int(4 + (10 - 4) * (y / (height * 0.65)))
            b = int(28 + (75 - 28) * (y / (height * 0.65)))
            draw.line([(0, y), (width, y)], fill=(r, g, b))
            
        # Stars twinkling
        for idx, (sx, sy, sz) in enumerate(stars):
            twinkle = (math.sin(f * (2 * math.pi / n_frames) + idx * 1.3) + 1) / 2
            alpha = int(100 + 155 * twinkle)
            color = (int(alpha * 0.9), int(alpha * 0.7), alpha)
            draw.rectangle([sx, sy, sx + sz - 1, sy + sz - 1], fill=color)
            
        # Shooting star
        if 3 <= f <= 8:
            prog = (f - 3) / 5
            ss_x = int(50 + prog * 200)
            ss_y = int(15 + prog * 50)
            draw.line([(ss_x - 35, ss_y - 9), (ss_x, ss_y)], fill=(0, 240, 255), width=2)
            draw.ellipse([ss_x - 1, ss_y - 1, ss_x + 1, ss_y + 1], fill=(255, 255, 255))
            
        # 2. Glowing Neon Sun
        sun_cx, sun_cy = 240, 115
        sun_r = 44
        sun_pulse = math.sin(f * (2 * math.pi / n_frames)) * 2
        cur_r = sun_r + sun_pulse
        
        for gr in range(int(cur_r) + 12, int(cur_r), -2):
            draw.ellipse([sun_cx - gr, sun_cy - gr, sun_cx + gr, sun_cy + gr], outline=(140, 20, 90))
            
        for sy in range(int(sun_cy - cur_r), int(sun_cy + cur_r)):
            if sy > 130:
                continue
            dx = math.sqrt(max(0, cur_r**2 - (sy - sun_cy)**2))
            t = (sy - (sun_cy - cur_r)) / (cur_r * 2)
            sr = 255
            sg = int(225 * (1 - t * 0.8))
            sb = int(80 + 120 * t)
            
            # Horizontal cut stripes
            if sy > sun_cy - 10:
                stripe_idx = int((sy - (sun_cy - 10)) / 6)
                if (sy - (sun_cy - 10)) % 6 < 2 + stripe_idx:
                    continue
            draw.line([(sun_cx - dx, sy), (sun_cx + dx, sy)], fill=(sr, sg, sb))
            
        # 3. Distant Mountains
        draw.polygon([(0, 130), (60, 95), (120, 120), (180, 85), (230, 130), (0, 130)], fill=(32, 8, 55))
        draw.line([(0, 130), (60, 95), (120, 120), (180, 85), (230, 130)], fill=(190, 30, 160), width=1)
        draw.polygon([(250, 130), (310, 85), (370, 118), (420, 92), (480, 130), (480, 130), (250, 130)], fill=(32, 8, 55))
        draw.line([(250, 130), (310, 85), (370, 118), (420, 92), (480, 130)], fill=(190, 30, 160), width=1)

        # 4. Horizon line
        horizon_y = 130
        draw.line([(0, horizon_y), (width, horizon_y)], fill=(0, 240, 255), width=2)
        
        # 5. 3D Perspective Grid
        draw.rectangle([(0, horizon_y + 1), (width, height)], fill=(6, 2, 16))
        for x_bottom in range(-120, width + 140, 35):
            draw.line([(sun_cx, horizon_y), (x_bottom, height)], fill=(160, 20, 180), width=1)
            
        offset = (f / n_frames)
        for i in range(10):
            z = (i + offset) / 10.0
            if z <= 0.05:
                continue
            y_proj = horizon_y + int((height - horizon_y) * (z ** 2.2))
            if y_proj <= height:
                cyan_mix = z
                r_line = int(120 * (1 - cyan_mix))
                g_line = int(30 * (1 - cyan_mix) + 240 * cyan_mix)
                b_line = int(180 * (1 - cyan_mix) + 255 * cyan_mix)
                w = 2 if z > 0.6 else 1
                draw.line([(0, y_proj), (width, y_proj)], fill=(r_line, g_line, b_line), width=w)
                
        p_im = im.convert('P', palette=Image.ADAPTIVE, colors=128)
        frames.append(p_im)
        
    frames[0].save(
        'public/images/Banner/cyber-matrix.gif',
        save_all=True,
        append_images=frames[1:],
        duration=90,
        loop=0,
        optimize=True
    )
    print("cyber-matrix.gif generated!")


def create_pixel_aurora():
    width, height = 480, 200
    frames = []
    n_frames = 16
    
    random.seed(101)
    stars = [(random.randint(0, width), random.randint(0, int(height * 0.7)), random.choice([1, 1, 2])) for _ in range(50)]
    
    # Generate tree silhouette points (pine trees)
    trees = []
    tx = 0
    while tx < width:
        th = random.randint(25, 48)
        tw = random.randint(12, 22)
        trees.append((tx, th, tw))
        tx += tw - random.randint(2, 6)
        
    for f in range(n_frames):
        im = Image.new('RGB', (width, height), (3, 10, 24))
        draw = ImageDraw.Draw(im)
        
        # 1. Night Sky Gradient (Deep sapphire to dark pine)
        for y in range(height):
            t = y / height
            r = int(3 + (6 - 3) * t)
            g = int(10 + (28 - 10) * t)
            b = int(24 + (45 - 24) * t)
            draw.line([(0, y), (width, y)], fill=(r, g, b))
            
        # Stars
        for idx, (sx, sy, sz) in enumerate(stars):
            twinkle = (math.sin(f * (2 * math.pi / n_frames) + idx * 0.9) + 1) / 2
            alpha = int(120 + 135 * twinkle)
            draw.rectangle([sx, sy, sx + sz - 1, sy + sz - 1], fill=(alpha, alpha, int(alpha * 1.1) if alpha < 230 else 255))
            
        # Bright Moon with soft glow
        moon_x, moon_y = 390, 48
        # Glow
        for gr in range(22, 10, -2):
            draw.ellipse([moon_x - gr, moon_y - gr, moon_x + gr, moon_y + gr], outline=(20, 50, 70))
        # Moon body
        draw.ellipse([moon_x - 11, moon_y - 11, moon_x + 11, moon_y + 11], fill=(240, 248, 255))
        # Subtle crater details
        draw.ellipse([moon_x - 5, moon_y - 4, moon_x - 1, moon_y], fill=(215, 228, 240))
        draw.ellipse([moon_x + 1, moon_y + 2, moon_x + 6, moon_y + 7], fill=(215, 228, 240))

        # 2. Aurora Borealis Ribbons (Flowing Green, Emerald, and Cyan)
        phase = f * (2 * math.pi / n_frames)
        
        # Ribbon 1 (Top cyan-green wave)
        for x in range(0, width, 2):
            wave1 = math.sin(x * 0.015 + phase) * 20 + math.cos(x * 0.025 - phase * 0.5) * 12
            base_y = 75 + wave1
            for dy in range(-18, 18, 2):
                alpha = max(0, 1.0 - abs(dy) / 18.0)
                ar = int(10 * alpha)
                ag = int(220 * alpha)
                ab = int(180 * alpha)
                draw.rectangle([x, int(base_y + dy), x + 1, int(base_y + dy + 1)], fill=(ar, ag, ab))
                
        # Ribbon 2 (Lower bright emerald-violet wave)
        for x in range(0, width, 2):
            wave2 = math.cos(x * 0.018 - phase * 1.2) * 24 + math.sin(x * 0.03 + phase * 0.8) * 10
            base_y = 110 + wave2
            for dy in range(-22, 22, 2):
                alpha = max(0, 1.0 - abs(dy) / 22.0)
                ar = int(40 * alpha)
                ag = int(245 * alpha)
                ab = int(120 * alpha)
                draw.rectangle([x, int(base_y + dy), x + 1, int(base_y + dy + 1)], fill=(ar, ag, ab))

        # 3. Floating Fireflies near tree line
        for fi in range(12):
            fx = int((fi * 40 + math.sin(phase + fi) * 15 + f * 4) % width)
            fy = int(140 + math.cos(phase * 1.5 + fi * 2) * 14)
            fglow = (math.sin(phase * 2 + fi * 1.7) + 1) / 2
            if fglow > 0.3:
                draw.ellipse([fx - 1, fy - 1, fx + 1, fy + 1], fill=(220, 255, 120))

        # 4. Pine Trees Silhouette across bottom
        draw.rectangle([(0, height - 15), (width, height)], fill=(4, 12, 10))
        for tx, th, tw in trees:
            tree_base = height - 10
            tree_top = tree_base - th
            # Triangle layers of pine tree
            p1 = [(tx, tree_base), (tx + tw // 2, tree_top), (tx + tw, tree_base)]
            draw.polygon(p1, fill=(2, 9, 8))
            # Slightly lighter inner layer
            p2 = [(tx + 2, tree_base), (tx + tw // 2, tree_top + 4), (tx + tw - 2, tree_base)]
            draw.polygon(p2, fill=(4, 14, 12))

        p_im = im.convert('P', palette=Image.ADAPTIVE, colors=128)
        frames.append(p_im)

    frames[0].save(
        'public/images/Banner/pixel-aurora.gif',
        save_all=True,
        append_images=frames[1:],
        duration=95,
        loop=0,
        optimize=True
    )
    print("pixel-aurora.gif generated!")


def create_solar_flare():
    width, height = 480, 200
    frames = []
    n_frames = 16
    
    random.seed(77)
    stars = [(random.randint(0, width), random.randint(0, height), random.choice([1, 1, 2])) for _ in range(60)]
    
    cx, cy = 240, 100
    core_r = 38
    
    for f in range(n_frames):
        im = Image.new('RGB', (width, height), (8, 2, 0))
        draw = ImageDraw.Draw(im)
        phase = f * (2 * math.pi / n_frames)
        
        # 1. Warm Nebula Dust background
        for y in range(height):
            for x in range(0, width, 4):
                dist = math.sqrt((x - cx)**2 + (y - cy)**2)
                neb = max(0, 1.0 - (dist / 260.0))
                r = int(10 + 70 * neb)
                g = int(2 + 25 * neb)
                b = int(2 + 8 * neb)
                draw.rectangle([x, y, x + 3, y], fill=(r, g, b))
                
        # Stars (warm yellow / orange)
        for idx, (sx, sy, sz) in enumerate(stars):
            twinkle = (math.sin(phase + idx * 1.1) + 1) / 2
            alpha = int(120 + 135 * twinkle)
            draw.rectangle([sx, sy, sx + sz - 1, sy + sz - 1], fill=(alpha, int(alpha * 0.8), int(alpha * 0.4)))
            
        # 2. Sweeping Light Rays from core
        for ray in range(8):
            angle = (ray * (math.pi / 4)) + (phase * 0.5)
            rx = cx + math.cos(angle) * 220
            ry = cy + math.sin(angle) * 110
            draw.line([(cx, cy), (rx, ry)], fill=(45, 18, 0), width=4)
            
        # 3. Outer Coronal Halo
        for gr in range(core_r + 32, core_r, -2):
            intensity = (gr - core_r) / 32.0
            r_c = int(255 * (1 - intensity * 0.6))
            g_c = int(140 * (1 - intensity))
            b_c = int(20 * (1 - intensity))
            draw.ellipse([cx - gr, cy - gr, cx + gr, cy + gr], outline=(r_c // 2, g_c // 2, b_c // 2))

        # 4. Orbiting Accretion Disk / Golden Ring Particles
        for pi in range(36):
            p_angle = (pi * (2 * math.pi / 36)) + phase
            ring_rx = 110 + math.sin(p_angle * 2) * 10
            ring_ry = 32
            px = int(cx + math.cos(p_angle) * ring_rx)
            py = int(cy + math.sin(p_angle) * ring_ry)
            # Front of ring is brighter, back is darker
            is_front = math.sin(p_angle) > 0
            p_color = (255, 230, 140) if is_front else (180, 100, 20)
            p_size = 2 if is_front else 1
            draw.ellipse([px - p_size, py - p_size, px + p_size, py + p_size], fill=p_color)

        # 5. Golden Solar Prominences / Flares looping out
        for fl in range(6):
            fl_angle = (fl * (2 * math.pi / 6)) - phase * 0.8
            fl_dist = core_r + 14 + math.sin(phase * 2 + fl) * 8
            fl_x = int(cx + math.cos(fl_angle) * fl_dist)
            fl_y = int(cy + math.sin(fl_angle) * fl_dist)
            draw.ellipse([fl_x - 6, fl_y - 6, fl_x + 6, fl_y + 6], fill=(255, 180, 30))

        # 6. Golden Star Core
        # Bright golden sphere
        for cr in range(core_r, 0, -1):
            t = cr / core_r
            cr_r = 255
            cr_g = int(245 - (1 - t) * 60)
            cr_b = int(160 * t)
            draw.ellipse([cx - cr, cy - cr, cx + cr, cy + cr], fill=(cr_r, cr_g, cr_b))
            
        # Core bright white center
        draw.ellipse([cx - 14, cy - 14, cx + 14, cy + 14], fill=(255, 255, 240))

        p_im = im.convert('P', palette=Image.ADAPTIVE, colors=128)
        frames.append(p_im)

    frames[0].save(
        'public/images/Banner/solar-flare.gif',
        save_all=True,
        append_images=frames[1:],
        duration=90,
        loop=0,
        optimize=True
    )
    print("solar-flare.gif generated!")


def create_deep_ocean():
    width, height = 480, 200
    frames = []
    n_frames = 16
    
    random.seed(55)
    bubbles = [(random.randint(0, width), random.randint(20, height), random.choice([2, 3, 4])) for _ in range(25)]
    plankton = [(random.randint(0, width), random.randint(0, height), random.choice([1, 2])) for _ in range(35)]
    
    for f in range(n_frames):
        im = Image.new('RGB', (width, height), (1, 10, 26))
        draw = ImageDraw.Draw(im)
        phase = f * (2 * math.pi / n_frames)
        
        # 1. Deep Ocean Gradient (Dark navy down to dark teal abyss)
        for y in range(height):
            t = y / height
            r = int(2 + 4 * t)
            g = int(14 + 32 * t)
            b = int(35 + 50 * t)
            draw.line([(0, y), (width, y)], fill=(r, g, b))
            
        # 2. Caustic Light Beams from surface
        for cb in range(6):
            beam_x = int(60 + cb * 75 + math.sin(phase + cb) * 20)
            draw.polygon([(beam_x - 10, 0), (beam_x + 10, 0), (beam_x + 60, height), (beam_x - 20, height)], fill=(8, 45, 75))

        # 3. Rising Bubbles with highlight
        for idx, (bx, by, bz) in enumerate(bubbles):
            cur_by = int((by - f * 8 - idx * 4) % height)
            cur_bx = int(bx + math.sin(phase + idx) * 6)
            draw.ellipse([cur_bx - bz, cur_by - bz, cur_bx + bz, cur_by + bz], outline=(0, 210, 255))
            draw.rectangle([cur_bx - 1, cur_by - bz + 1, cur_bx, cur_by - bz + 2], fill=(255, 255, 255))

        # 4. Bioluminescent Spores / Plankton
        for idx, (px, py, pz) in enumerate(plankton):
            cur_py = int((py - f * 3) % height)
            cur_px = int(px + math.cos(phase * 1.2 + idx) * 8)
            p_glow = (math.sin(phase * 2 + idx) + 1) / 2
            alpha = int(120 + 135 * p_glow)
            draw.ellipse([cur_px - pz, cur_py - pz, cur_px + pz, cur_py + pz], fill=(0, alpha, int(alpha * 0.9)))

        # 5. Bioluminescent Jellyfish
        jellyfishes = [
            (140, 90, 22, 1.0, (0, 240, 255)),
            (320, 120, 18, 1.4, (160, 100, 255)),
            (230, 55, 14, 0.8, (0, 255, 200)),
            (420, 85, 15, 1.2, (100, 200, 255))
        ]
        
        for jx_base, jy_base, jr, speed, jcolor in jellyfishes:
            # Jellyfish swimming pulse
            pulse = math.sin(phase * speed)
            jy = int(jy_base - f * 2.5 * speed) % (height + 40) - 20
            jx = int(jx_base + math.sin(phase + jx_base) * 8)
            
            # Bell dome (squash and stretch)
            dome_w = int(jr * (1.0 + pulse * 0.15))
            dome_h = int(jr * (0.8 - pulse * 0.15))
            
            # Bell outer glow
            draw.ellipse([jx - dome_w - 4, jy - dome_h - 4, jx + dome_w + 4, jy + dome_h + 4], outline=(jcolor[0] // 3, jcolor[1] // 3, jcolor[2] // 3))
            # Bell inner body
            draw.ellipse([jx - dome_w, jy - dome_h, jx + dome_w, jy + dome_h], fill=jcolor)
            draw.ellipse([jx - dome_w + 3, jy - dome_h + 3, jx + dome_w - 3, jy + dome_h - 2], fill=(240, 255, 255))
            
            # Tentacles undulating
            for t_idx in range(-3, 4):
                tx_start = jx + int(t_idx * (dome_w / 3.5))
                ty_start = jy + dome_h - 2
                points = [(tx_start, ty_start)]
                for seg in range(1, 5):
                    seg_y = ty_start + seg * 8
                    seg_x = tx_start + int(math.sin(phase * 2 + seg + t_idx) * (3 + seg))
                    points.append((seg_x, seg_y))
                draw.line(points, fill=jcolor, width=1)

        p_im = im.convert('P', palette=Image.ADAPTIVE, colors=128)
        frames.append(p_im)

    frames[0].save(
        'public/images/Banner/deep-ocean.gif',
        save_all=True,
        append_images=frames[1:],
        duration=95,
        loop=0,
        optimize=True
    )
    print("deep-ocean.gif generated!")


def create_sakura_twilight():
    width, height = 480, 200
    frames = []
    n_frames = 16
    
    random.seed(88)
    # Generate petals
    petals = []
    for _ in range(40):
        px = random.randint(-40, width + 40)
        py = random.randint(-20, height + 20)
        speed = random.uniform(2.5, 4.5)
        size = random.choice([3, 4, 5])
        angle_offset = random.uniform(0, math.pi * 2)
        petals.append([px, py, speed, size, angle_offset])
        
    for f in range(n_frames):
        im = Image.new('RGB', (width, height), (26, 12, 42))
        draw = ImageDraw.Draw(im)
        phase = f * (2 * math.pi / n_frames)
        
        # 1. Twilight Sky Gradient (Deep violet to warm dusk magenta to peach)
        for y in range(height):
            t = y / height
            if t < 0.5:
                # Indigo to magenta
                st = t / 0.5
                r = int(26 + (115 - 26) * st)
                g = int(12 + (42 - 12) * st)
                b = int(48 + (90 - 48) * st)
            else:
                # Magenta to soft peach
                st = (t - 0.5) / 0.5
                r = int(115 + (220 - 115) * st)
                g = int(42 + (120 - 42) * st)
                b = int(90 + (130 - 90) * st)
            draw.line([(0, y), (width, y)], fill=(r, g, b))
            
        # 2. Glowing Soft Moon
        moon_cx, moon_cy = 110, 65
        moon_r = 34
        for gr in range(moon_r + 14, moon_r, -2):
            draw.ellipse([moon_cx - gr, moon_cy - gr, moon_cx + gr, moon_cy + gr], outline=(180, 100, 140))
        draw.ellipse([moon_cx - moon_r, moon_cy - moon_r, moon_cx + moon_r, moon_cy + moon_r], fill=(255, 240, 230))
        # Subtle moon crater/shadow
        draw.ellipse([moon_cx - 15, moon_cy - 10, moon_cx - 5, moon_cy], fill=(245, 225, 215))

        # 3. Distant Mountain Silhouette (Mount Fuji inspired)
        # Main mountain in center-right
        fuji_pts = [
            (210, height), (270, 95), (290, 95), (350, height)
        ]
        draw.polygon([(0, height), (150, 140), (220, 155), (380, 135), (480, height)], fill=(40, 16, 55))
        draw.polygon(fuji_pts, fill=(50, 20, 65))
        # Snowcap silhouette
        snow_pts = [
            (265, 115), (270, 95), (290, 95), (295, 115),
            (285, 118), (280, 114), (275, 118)
        ]
        draw.polygon(snow_pts, fill=(240, 220, 235))

        # 4. Torii Gate / Pagoda silhouette on a hill at right
        hill_pts = [(360, height), (410, 145), (480, 155), (480, height)]
        draw.polygon(hill_pts, fill=(24, 8, 35))
        # Torii gate
        gx, gy = 420, 130
        draw.line([(gx - 10, gy), (gx + 10, gy)], fill=(18, 5, 28), width=3) # top beam
        draw.line([(gx - 8, gy + 4), (gx + 8, gy + 4)], fill=(18, 5, 28), width=2) # second beam
        draw.line([(gx - 6, gy), (gx - 6, gy + 20)], fill=(18, 5, 28), width=2) # left pillar
        draw.line([(gx + 6, gy), (gx + 6, gy + 20)], fill=(18, 5, 28), width=2) # right pillar

        # 5. Drifting Sakura Petals
        for p in petals:
            # Advance petal position
            cur_x = int((p[0] - f * p[2] * 2.5) % (width + 60)) - 30
            cur_y = int((p[1] + f * p[2] * 1.2 + math.sin(phase + p[4]) * 8) % (height + 30)) - 10
            sz = p[3]
            petal_rot = phase + p[4]
            
            # Petal shape (elongated angled oval)
            pw = int(sz * (1.2 + math.sin(petal_rot) * 0.3))
            ph = int(sz * (0.8 + math.cos(petal_rot) * 0.3))
            
            # Beautiful rose/pink petal color
            draw.ellipse([cur_x - pw, cur_y - ph, cur_x + pw, cur_y + ph], fill=(255, 175, 204))
            draw.ellipse([cur_x - pw + 1, cur_y - ph + 1, cur_x + pw - 1, cur_y + ph - 1], fill=(255, 215, 230))

        p_im = im.convert('P', palette=Image.ADAPTIVE, colors=128)
        frames.append(p_im)

    frames[0].save(
        'public/images/Banner/sakura-twilight.gif',
        save_all=True,
        append_images=frames[1:],
        duration=95,
        loop=0,
        optimize=True
    )
    print("sakura-twilight.gif generated!")


if __name__ == '__main__':
    create_cyber_matrix()
    create_pixel_aurora()
    create_solar_flare()
    create_deep_ocean()
    create_sakura_twilight()
    print("All 5 unique banners generated successfully!")
