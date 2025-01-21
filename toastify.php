
  <style>
    
    :root {
      --success-color: #34c759;
      --error-color: #ff3b30;
    }

    #toast-container {
      position: fixed;
      top: 20px;
      left: 10px;
      z-index: 1000;
      width: 90%;
      max-width: 300px;
      display: flex;
      flex-direction: column-reverse;
      gap: 8px;
      pointer-events: none;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;

    }

    .toast {
      background-color: var(--hover-clr);
      backdrop-filter: blur(10px);
      border-radius: 9px;
      padding: 16px;
      display: flex;
      align-items: flex-start;
      gap: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1),
                  0 0 1px rgba(0, 0, 0, 0.05);
      transform: translateY(100%) scale(0.9);
      opacity: 0;
      pointer-events: auto;
      position: relative;
      overflow: hidden;
    }

    .toast.show {
      animation: slideUp2 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
    }

    .toast.hide {
      animation: slideDown 0.2s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
    }

    .toast-icon {
      flex-shrink: 0;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .toast.success .toast-icon {
      color: #34c759;
    }

    .toast.error .toast-icon {
      color: #ff3b30;
    }

    .toast-content {
      flex-grow: 1;
      margin-right: 8px;
    }

    .toast-content h4 {
      margin: 0;
      font-size: 15px;
      font-weight: 600;
      color: #e6e6ef;

    }

    .toast-content p {
      margin: 4px 0 0;
      font-size: 13px;
      color: #b0b3c1;

    }

    .dismiss-btn {
      background: none;
      border: none;
      padding: 4px;
      color: #8e8e93;
      cursor: pointer;
      border-radius: 50%;
      flex-shrink: 0;
      transition: background-color 0.2s;
    }

    .dismiss-btn:hover {
      background-color: rgba(142, 142, 147, 0.1);
    }

    .progress-bar {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background: rgba(142, 142, 147, 0.1);
      overflow: hidden;
    }

    .progress {
      height: 100%;
      width: 100%;
      background: currentColor;
      animation: progress 3s linear forwards;
    }

    .toast.success .progress {
      color: var(--success-color);
    }

    .toast.error .progress {
      color: var(--error-color);
    }

    @keyframes slideUp2 {
      from {
        transform: translateX(-100%) scale(0.9);
        opacity: 0;
      }
      to {
        transform: translateX(0) scale(1);
        opacity: 1;
      }
    }

    @keyframes slideDown {
      from {
        transform: translateX(0) scale(1);
        opacity: 1;
      }
      to {
        transform: translateX(-100%) scale(0.9);
        opacity: 0;
      }
    }

    @keyframes progress {
      from { transform: translateX(0); }
      to { transform: translateX(-100%); }
    }


  </style>
  <div id="toast-container"></div>
