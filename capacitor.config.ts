import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.mempar.app',
  appName: 'Mempar',
  webDir: 'public',
  server: {
    url: 'https://mempartestes.infotech.app.br',
    cleartext: true
  },
  plugins: {
    CapacitorAssets: {
      icon: "assets/icon.png",
      splash: "assets/splash.png"
    }
  }
};

export default config;
