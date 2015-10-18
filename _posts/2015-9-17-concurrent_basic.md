---
layout: post

title: JAVA系列之－－并发编程（基础）
subtitle: "容器、队列、同步工具类、线程池"
cover_image: blog-cover.jpg
article: 15091701

author:
  name: Skyouth
  weibo: skyouth
  bio: Runnable,cmbchina
  
tags: [技术]
---

# 基础
1. 线程有哪些状态？
	新建状态new，刚创建的线程
	就绪状态runnable，调用start方法后，不一定马上能抢到cpu时间，因此不一定能马上执行run方法，这时的状态位runnable
		运行状态running
	阻塞状态blocked	死亡状态dead2. Thread和runnable实现线程的区别，优劣势？	java单继承，所以继承自thread就不能继承其他超类了；		runnable适合资源的共享（其实这个有点牵强，线程内部的资源，很少分到多个线程里去共享使用吧）3. 怎么解决多线程资源共享冲突？
	synchronized、lock、CAS机制4. 用户线程和守护线程（Daemon）的区别		User和Daemon两者几乎没有区别，唯一的不同之处就在于虚拟机的离开：如果 User Thread已经全部退出运行了，只剩下Daemon Thread存在了，虚拟机也就退出了。# 同步容器类和并发容器类
1. 同步容器类，如Vector、hashtable，线程安全，但是通过对容器的所有状态的访问都串行化实现，因此并发性很差。（通过vector和hashtable的代码可以看出，他们的synchronized的粒度都是在方法上的，这样同时只有一个线程可以调用这个对象的方法）2. 并发容器类，专为并发线程而设计，并发粒度更细。	ConcurrentHashMap：采用分段锁的机制，实现任意数量的读取线程可以并发地修改Map，执行读取操作的线程和写入操作的线程也可以并发地访问Map，并且一定数量的写入线程可以并发地修改Map。因为锁不是在对象上，而是在各个片段上；
	CopyOnWriteArrayList：每次修改时都会创建新的副本，而迭代是在原有基础数组上进行，它不会被修改，因此容器的修改不会对迭代造成影响。所以，多个线程可以同时对这个容器进行迭代，他们不会彼此干扰，也不会和修改容器的线程相互干扰。每次修改需要创建新副本，因此适合修改少而迭代多的场景3. ConcurrentHashMap为什么比HashTable能更好的支持并发？	Hashtable的并发控制粒度是在方法上的，使用synchronized，因此访问是串行性的，并发性差，而ConcurrentHashMap是采用分段锁的机制，并发性更好。上面已有详细说明，不再详述。4. ConcurrentHashMap的get方法是否使用锁？原理是什么？	
	他不在整个get方法上使用锁，因为读取是从片段上读取，只需在片段中读取值的时候上锁就可以了。（key值没变，所以hash值也不会变，那key值在map中的位置也不会变，只要确保读取时的value不被修改即可）#阻塞队列和生产者-消费者模式**<a href='http://wsmajunfeng.iteye.com/blog/1629354'>BlockingQueue</a>（interface）**：
put/take方法阻塞生产者或消费者，offer/poll方法不阻塞。**LinkedBlockingQueue –LinkedList**
基于链表的阻塞队列，同ArrayListBlockingQueue类似，其内部也维持着一个数据缓冲队列（该队列由一个链表构成），当生产者往队列中放入一个数据时，队列会从生产者手中获取数据，并缓存在队列内部，而生产者立即返回；只有当队列缓冲区达到最大值缓存容量时（LinkedBlockingQueue可以通过构造函数指定该值），才会阻塞生产者队列，直到消费者从队列中消费掉一份数据，生产者线程会被唤醒，反之对于消费者这端的处理也基于同样的原理。而LinkedBlockingQueue之所以能够高效的处理并发数据，还因为其对于生产者端和消费者端分别采用了独立的锁来控制数据同步，这也意味着在高并发的情况下生产者和消费者可以并行地操作队列中的数据，以此来提高整个队列的并发性能。如果创建队列时不指定大小，那队列大小就默认为Integer.MAX_VALUE，这种情况下，如果进入队列大于出队列的速度，容易导致内存溢出。**ArrayBlockingQueue – ArrayList**
在ArrayBlockingQueue内部，维护了一个定长数组，以便缓存队列中的数据对象，这是一个常用的阻塞队列，除了一个定长数组外，ArrayBlockingQueue内部还保存着两个整形变量，分别标识着队列的头部和尾部在数组中的位置。ArrayBlockingQueue在生产者放入数据和消费者获取数据，都是共用同一个锁对象，由此也意味着两者无法真正并行运行，这点尤其不同于LinkedBlockingQueue。ArrayBlockingQueue和LinkedBlockingQueue间还有一个明显的不同之处在于，前者在插入或删除元素时不会产生或销毁任何额外的对象实例，而后者则会生成一个额外的Node对象。这在长时间内需要高效并发地处理大批量数据的系统中，其对于GC的影响还是存在一定的区别。而在创建ArrayBlockingQueue时，我们还可以控制对象的内部锁是否采用公平锁，默认采用非公平锁。**PriorityBlockingQueue**
**SynchronousQueue**双端队列：**Deque**  **BlockingDeque**#同步工具类
1. CountDownLatch（闭锁）	用于一个线程阻塞等待其他线程全部结束以后再继续执行。阻塞等待调用CountDownLatch的await方法，CountDownLatch有一个计数，其他线程没结束一个，就countdown一次，直到计数为0，阻塞线程即可唤醒继续执行2. FutureTask(Future的实现类)	当一个FutureTask启动以后，get方法可以阻塞直到FutureTask得到结果。这样，当有多项事情需要完成时，可以提前启动FutureTask要做的事情，提高执行效率。3. Semaphore	技术信号量。一个semaphore维持一组许可（并非实际的对象），当调用acquire时就消耗掉一个许可，而调用release时就释放一个许可，没有许可时就阻塞。这样可以达到对一个资源池的管理作用。例子可以直接见jdk注释。4. CyclicBarrier（栅栏）	用于线程之间的相互等待，在await处阻塞，直到所有其他线程都到达await所设置的await处，阻塞解除。CyclicBarrier是可以循环使用的，但是CountDownLatch只能使用一次。注意，这里的相互等待的线程数是固定的。这点又和CountDownLatch相似了。5. 自定义同步，wait/notify/notifyAll or condition？	notify的时候，怎么去唤醒线程，要唤醒哪些线程条件队列的使用，何时使用notify，何时使用notifyall，优先选择notifyall	* wait方法别用来使线程等待某个条件，它必须在同步块内部被调用，这个同步块通常会锁定当前对象实例。下面是这个模式的标准使用方式：		<pre><code>synchronized（this）   		{    	while(condition)        Object.wait;......	}do {   if (U.compareAndSwapInt(this, STATUS, s, s | SIGNAL)) {               synchronized (this) {                   if (status >= 0) {                       try {                           wait();                       } catch (InterruptedException ie) {                           interrupted = true;                       }                   } elsenotifyAll();                }            }        } while ((s = status) >= 0);</code></pre>	* 始终使用while循环来调用wait方法，永远不要在循环之外调用wait方法。原因是尽管条件并不满足被唤醒条件，但是由于其它线程意外调用notifyAll()方法会导致被阻塞线程意外唤醒，此时执行条件并不满足，它将破坏被锁保护的约定关系，导致约束失效，引起意想不到的结果；	* 唤醒线程，应该使用notify还是notifyAll，当你不知道究竟该调用哪个方法时，保守的做法是调用notifyAll唤醒所有等待的线程。从优化的角度看，如果处于等待的所有线程都在等待同一个条件，而每次只有一个线程可以从这个条件中被唤醒，那么就应该选择调用notify。#线程池工具
1. 接口ExecutorService/Executor（继承自接口Executor）2. ForkJoinPool（ExecutorService的实现类）（jdk1.7开始）	
	Fork/join模式，分而治之思想，将任务拆分成多个子任务进行。Work Stealing机制，在该线程池的每个线程中会维护一个队列来存放需要被执行的任务。当线程自身队列中的任务都执行完毕后，它会从别的线程中拿到未被执行的任务并帮助它执行。当任务的任务量均衡时，选择ThreadPoolExecutor往往更好，反之则选择ForkJoinPool。同时ForkJoinPool的任务有两种，RecursiveTask和RecursiveAction。RecursiveTask是需要汇总结果，即使用fork分，使用join合，而RecursiveAction只需要分割处理任务，因此不需要获取返回值，直接invokeall所有任务即可。参考：
	<a href='http://blog.csdn.net/dm_vincent/article/details/39505977'>线程及同步的性能 - 线程池/ThreadPoolExecutors/ForkJoinPool</a>
	<a href='http://www.iteye.com/topic/1117483'>ForkJoinPool VS ExecutorService</a>3. ThreadPoolExecutor（ExecutorService的实现类）	
	Executors. newCachedThreadPool corePoolSize=0, maximumPoolSize=max线程池的大小过大，会导致大量线程在相对很少的cpu和内存资源上发生竞争，大小的合理设置依赖于cpu的个数，等待时间与计算时间的比值等信息4. ThreadPoolExecutor的溢出策略RejectExecutionHandler	* AbortPolicy：拒绝，抛异常	* DiscardPolicy：废弃任务	* DiscardOldestPolicy：废弃最旧的任务	* CallerRunsPolicy：在线程池以外直接执行