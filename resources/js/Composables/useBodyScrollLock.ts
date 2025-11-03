// useBodyScrollLock.ts (Vue 3 composable)
import { onMounted, onBeforeUnmount } from 'vue';

export function useBodyScrollLock(activeRef: { value: boolean }) {
  let scrollY = 0;

  const lock = () => {
    // sudah di-lock? skip
    if (document.body.style.position === 'fixed') return;

    // simpan posisi scroll sekarang
    scrollY = window.scrollY || window.pageYOffset;

    // hitung lebar scrollbar untuk hindari layout shift
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    if (scrollbarWidth > 0) {
      document.body.style.paddingRight = `${scrollbarWidth}px`;
    }

    // kunci body
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.width = '100%'; // cegah “ngelebar”
    document.body.style.overflow = 'hidden'; // jaga-jaga
  };

  const unlock = () => {
    if (document.body.style.position !== 'fixed') return;

    // lepas kunci
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.width = '';
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';

    // kembalikan posisi scroll semula
    window.scrollTo(0, scrollY);
  };

  // opsional: auto apply saat mount/unmount jika activeRef sudah true
  onMounted(() => { if (activeRef.value) lock(); });
  onBeforeUnmount(() => { if (activeRef.value) unlock(); });

  return { lock, unlock };
}
