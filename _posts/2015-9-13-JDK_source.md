---
layout: post

title: JAVA系列之－－JDK源码
subtitle: "JDK源码解读"
cover_image: blog-cover.jpg
article: 15091301

author:
  name: Skyouth
  weibo: skyouth
  bio: Runnable,cmbchina
  
tags: [技术]
---

# Jdk5\jdk6\jdk7\jdk8的区别
JDK5开始新增了泛型、自动装箱\拆箱、for-each、变长参数等；
jdk6和jdk5相比的新特性有： 1、instrumentation 在 Java SE 6 里面，instrumentation 包被赋予了更强大的功能：启动后的 instrument、本地代码 instrument，以及动态改变 classpath 等等。 2、Http有所增强 3、 Java 管理扩展（JMX）架构及其框架，以及在 Java SE 5 中新引入的 JMX API -- java.lang.management 包 4、JDK 6 中新增加编译器 API（JSR 199）。利用此 API，您可以在运行时调用 Java 编译器，可以编译不同形式的源代码文件，还可以采集编译器的诊断信息。 5、Java DB 和 JDBC 4.0 6、对脚本语言的支持 Java SE 6 新引入的对 JSR 223 的支持，它旨在定义一个统一的规范，使得 Java 应用程序可以通过一套固定的接口与各种脚本引擎交互，从而达到在 Java 平台上调用各种脚本语言的目的。 7、XML API 与 Web 服务 Java SE 6 中提供的 XML 处理框架，以及在此框架之上结合注释（Annotation）技术，所提供的强大的针对 Web 服务的支持
参考：<a href='http://www.ibm.com/developerworks/cn/java/j-lo-jdk7-1/'>JDK7新特性</a>
# arraylist\linkedlist
Arraylist的底层为一个object数组，在初始化时即初始化了一个数组，新增元素即在数组上赋值，如果数组满了，还需要对数组进行扩容，如果删除元素，还需要对数组进行重新的整理，因此，arraylist的增删改性能较差，但是读取直接根据数组坐标读取，因此性能优越；反之，linkedlist是一个双向列表(Deque,继承了Queue)，增删改性能优越，读取性能差，因为读取需要遍历，增删改只需修改链表即可。

# hashmap\hashset\hashtable
Hashmap的底层是一个Entry数组，每个entry带有对下一个元素的引用，即链表。所以整体的看，他是一个数组和链表的结合体（<a href='http://zha-zi.iteye.com/blog/1124484'>链表散列</a>，散列即hash）。如图：
<img src='/images/hashmap.png'>

初始化时，Hashmap的容量肯定为2的n次方，即entry[2^n]，默认为entry[16]。当新增一个键值对时，先计算键值的的散列值，使其平均分布在entry数组上，不同的key值，散列值可能相同，这时就放在该数组位置的链表头上，链表头指向原链表头。如果hashmap的实际容量超过了数组长度的影响因子倍（loadFactor，默认0.75）（或者是初始容量，jdk7），则entry将进行扩容，同时将原来列表进行重新计算散列值分布于新的entry中。散列值的计算如下：
    <pre><code>h = key.hashCode();h ^= (h >>> 20) ^ (h >>> 12);h = h ^ (h >>> 7) ^ (h >>> 4);h = h & (length-1);//hashmap的数组一定是2的n次方，所以h&（length-1）=h%length，所以散列值即是hash取模。此种情况下，按照Bruce Eckel给出的数据，位运算的效率是直接取模的效率的大约5～8倍，但是根据我的测试，只能提升一倍。。。
</code></pre>注意，由于hashmap不是线程安全的，并且在这里的扩容，还会对链表进行反转，在高并发的场景下，扩容可能导致死循环。hashset的底层就是hashmap，通过hashmap的key值唯一性来实现hashset的唯一性。而hashmap的所有key对应的值都为Object对象。Hashtable和hashmap的区别主要有：1、	hashtable是线程安全的，而hashmap是线程不安全的；2、	hash算法不同：hashtable的key散列值计算如下：int index = (key.hashCode()& 0x7FFFFFFF) % tab.length;3、	hashtable继承于Dictionary（比较陈旧，由map替代），hashmap继承于AbstractMap（map的抽象实现类）；4、	hashtable的key不能为空，但是hashmap的key是可以为空的
#linkedhashmap
他是hashmap的子类。他保持hashmap的结构，同时他维护了一个双向链表，他的entry是一个存在双向指针的结点，在遍历时，可以保持插入的顺序（或者，accessOrder=true，访问的顺序）
#weakhashmap\WeakReference\ReferenceQueue
java引用有强引用，即平时用得最多的引用；弱引用，即weakreference，在垃圾回收器线程扫描它所管辖的内存区域的过程中，一旦发现了只具有弱引用的对象，不管当前内存空间足够与否，都会回收它的内存，因此gc时，是可以访问weakreference对象的。此外，还有软引用softreference，是只有在内存不足时才会回收。WeakHashMap的entry都是weak reference，假设，WeakHashMap对象里面已经保存了很多对象的引用。JVM使用进行CMS GC的时候，会创建一个ConcurrentMarkSweepThread（简称CMST）线程去进行GC，ConcurrentMarkSweepThread线程被创建的同时会创建一个SurrogateLockerThread（简称SLT）线程并且启动它，SLT启动之后，处于等待阶段。CMST开始GC时，会发一个消息给SLT让它去获取Java层Reference对象的全局锁：lock。直到CMS GC完毕之后，JVM会将WeakHashMap中所有被回收的对象所属的WeakReference(weakhashmap的entry继承了WeakReference)容器对象放入到Reference的pending 属性当中（每次GC完毕之后，pending属性基本上都不会为null了），然后通知SLT释放并且notify全局锁: lock。此时激活了ReferenceHandler线程的run方法，使其脱离wait状态，开始工作了。ReferenceHandler这个线程会将pending中的所有WeakReference对象都移动到它们各自的列队当中，比如当前这个WeakReference属于某个WeakHashMap对象，那么它就会被放入相应的ReferenceQueue列队里面（该列队是链表结构）。当GC之后，WeakHashMap对象里面get、put数据或者调用size方法的时候，WeakHashMap比HashMap多了一个 expungeStaleEntries()方法. expungeStaleEntries方法 就是将ReferenceQueue列队中的WeakReference依依poll出来去和Entry[]数据做比较，如果发现相同的，则说明这个Entry所保存的对象已经被GC掉了，那么将Entry[]内的Entry对象剔除掉，这样就把被GC掉的 WeakReference对应的Entry从WeakHashMap中移除了。